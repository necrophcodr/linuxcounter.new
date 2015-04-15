<?php

namespace Syw\Front\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Syw\Front\MainBundle\Util\XmlToArrayParser;

class StatsController extends BaseController
{
    /**
     * @Route("/statistics")
     * @Method("GET")
     *
     * @Template()
     */
    public function indexAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics mainpage', array(), 'syw_front_main_stats_index');
        $title1 = $metatitle;
        $title2 = $this->get('translator')->trans('The estimation of linux users', array(), 'syw_front_main_stats_index');
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();
        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title1' => $title1,
            'title2' => $title2,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/users")
     * @Method("GET")
     *
     * @Template()
     */
    public function usersAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the Linux users', array(), 'syw_front_main_stats_users');
        $title = $metatitle;
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();

        $em = $this->getDoctrine()->getManager();
        $dql   = "SELECT b FROM SywFrontMainBundle:Cities b WHERE b.usernum >= 1 ORDER BY b.usernum DESC, b.name ASC";
        $ent_cities = $em->createQuery($dql);
        $dql   = "SELECT a FROM SywFrontMainBundle:Countries a WHERE a.usersnum >= 1 ORDER BY a.usersnum DESC, a.name ASC";
        $ent_countries = $em->createQuery($dql);
        $knpPaginator = $this->get('knp_paginator');
        $paginationAAA = $knpPaginator->paginate(
            $ent_cities,
            $this->get('request')->query->get('pageAAA', 1), // page number
            15, // limit per page
            array(
                'pageParameterName' => 'pageAAA',
                'sortFieldParameterName' => 'sortAAA',
                'sortDirectionParameterName' => 'directionAAA',
            )
        );
        $paginationBBB = $knpPaginator->paginate(
            $ent_countries,
            $this->get('request')->query->get('pageBBB', 1), // page number
            15, // limit per page
            array(
                'pageParameterName' => 'pageBBB',
                'sortFieldParameterName' => 'sortBBB',
                'sortDirectionParameterName' => 'directionBBB',
            )
        );

        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'paginationAAA' => $paginationAAA,
            'paginationBBB' => $paginationBBB,
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title' => $title,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/machines")
     * @Method("GET")
     *
     * @Template()
     */
    public function machinesAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the Linux machines', array(), 'syw_front_main_stats_machines');
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();

        // accounts on the machines
        $title1 = $this->get('translator')->trans('Statistics about the number of accounts on the machines', array(), 'syw_front_main_stats_machines');
        $xml = file_get_contents("xml/machines_accounts.xml");
        $domObj = new xmlToArrayParser($xml);
        $domArr = $domObj->array;
        $stats['accounts'] = array();
        $gesamt = 0;
        for ($a = 0; $a<count($domArr['statistics']['line']); $a++) {
            $gesamt += intval($domArr['statistics']['line'][$a]['number']);
        }
        for ($a = 0; $a<count($domArr['statistics']['line']); $a++) {
            $line = $domArr['statistics']['line'][$a];
            $percent = round((100/$gesamt) * intval($line['number']), 2);
            $stats['accounts'][$a]['accounts'] = $line['accounts'];
            $stats['accounts'][$a]['number'] = $line['number'];
            $stats['accounts'][$a]['percent'] = $percent;
        }
        // end accounts on the machines

        // countries of the machines
        $title2 = $this->get('translator')->trans('Statistics about the number of machines per country', array(), 'syw_front_main_stats_machines');
        $xml = file_get_contents("xml/machines_countries.xml");
        $domObj = new xmlToArrayParser($xml);
        $domArr = $domObj->array;
        $stats['countries'] = array();
        $gesamt = 0;
        for ($a = 0; $a<count($domArr['statistics']['line']); $a++) {
            $gesamt += intval($domArr['statistics']['line'][$a]['machines']);
        }
        for ($a = 0; $a<count($domArr['statistics']['line']); $a++) {
            $line = $domArr['statistics']['line'][$a];
            $percent = round((100/$gesamt) * intval($line['machines']), 2);

            $country = null;
            unset($country);
            $country = $this->get('doctrine')
                ->getRepository('SywFrontMainBundle:Countries')
                ->findOneBy(array('code' => strtolower($line['country'])));

            $stats['countries'][$a]['country'] = $country->getName();
            $stats['countries'][$a]['number'] = $line['machines'];
            $stats['countries'][$a]['percent'] = $percent;
        }
        // end accounts on the machines

















        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title1' => $title1,
            'title2' => $title2,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/distributions")
     * @Method("GET")
     *
     * @Template()
     */
    public function distributionsAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the Linux distributions', array(), 'syw_front_main_stats_distributionsx');
        $title = $metatitle;
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();
        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title' => $title,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/kernels")
     * @Method("GET")
     *
     * @Template()
     */
    public function kernelsAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the Linux kernels', array(), 'syw_front_main_stats_kernels');
        $title = $metatitle;
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();
        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title' => $title,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/uptimes")
     * @Method("GET")
     *
     * @Template()
     */
    public function uptimesAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the machine uptimes', array(), 'syw_front_main_stats_uptimes');
        $title = $metatitle;
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();
        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title' => $title,
            'languages' => $languages,
            'user' => $user,
            'stats' => $stats
        );
    }

    /**
     * @Route("/statistics/counter")
     * @Method("GET")
     *
     * @Template()
     */
    public function counterAction()
    {
        $metatitle = $this->get('translator')->trans('Statistics about the Linux Counter itself', array(), 'syw_front_main_stats_counter');
        $title1 = $metatitle;
        $title2 = $this->get('translator')->trans('Statistics about the registrations', array(), 'syw_front_main_stats_counter');
        $languages = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findBy(array('active' => 1), array('language' => 'ASC'));

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
        } else {
            $user = null;
        }
        $stats = array();
        $stats['guess'] = $this->getGuessStats();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select('count(user.id)');
        $qb->from('SywFrontMainBundle:User', 'user');
        $uCount = $qb->getQuery()->getSingleScalarResult();
        $stats['usernum'] = $uCount;

        $qb = $em->createQueryBuilder();
        $qb->select('count(machines.id)');
        $qb->from('SywFrontMainBundle:Machines', 'machines');
        $mCount = $qb->getQuery()->getSingleScalarResult();
        $stats['machinenum'] = $mCount;

        $mostcity = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Cities')
            ->findBy(
                array(),
                array('usernum' => 'DESC'),
                1,
                0
            );
        $stats['mostcity'] = $mostcity[0]->getName();
        $stats['cityusernum'] = $mostcity[0]->getUserNum();
        $code = $mostcity[0]->getIsoCountryCode();
        $country = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Countries')
            ->findOneBy(array('code' => strtolower($code)));
        $stats['citycountry'] = $country->getName();

        // Chart about User registrations per Month
        unset($data1);
        $registrations = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:StatsMachines')
            ->findBy(array(), array('month' => 'ASC'));
        foreach ($registrations as $reg) {
            $y = $reg->getMonth()->format('Y');
            $m = $reg->getMonth()->format('m');
            $d = $reg->getMonth()->format('d');
            $data1[] = array(
                (($reg->getMonth()->format('U') + 86400) * 1000),
                $reg->getNum()
            );
        }
        unset($data2);
        $registrations = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:StatsRegistration')
            ->findBy(array(), array('month' => 'ASC'));
        foreach ($registrations as $reg) {
            $y = $reg->getMonth()->format('Y');
            $m = $reg->getMonth()->format('m');
            $d = $reg->getMonth()->format('d');
            $data2[] = array(
                (($reg->getMonth()->format('U') + 86400) * 1000),
                $reg->getNum()
            );
        }
        $series = array(
            array(
                "type" => "area",
                "name" => $this->get('translator')->trans('User Registrations', array(), 'syw_front_main_stats_counter'),
                "data" => $data2
            ),
            array(
                "type" => "area",
                "name" => $this->get('translator')->trans('Machine Registrations', array(), 'syw_front_main_stats_counter'),
                "data" => $data1
            )
        );
        $chart_registrations_per_month = new Highchart();
        $chart_registrations_per_month->chart->renderTo('chart_registrations_per_month');
        $chart_registrations_per_month->chart->zoomType('x');
        $chart_registrations_per_month->chart->type('line');
        $chart_registrations_per_month->title->text($this->get('translator')->trans('Registrations per month', array(), 'syw_front_main_stats_counter'));
        $chart_registrations_per_month->subtitle->text($this->get('translator')->trans('Click and drag in the plot area to zoom in', array(), 'syw_front_main_stats_counter'));
        $chart_registrations_per_month->xAxis->title(array('text'  => $this->get('translator')->trans('Date', array(), 'syw_front_main_stats_counter')));
        $chart_registrations_per_month->xAxis->type('datetime');
        $chart_registrations_per_month->xAxis->minRange(14 * 24 * 3600000 * 30); // 14 Monate
        $chart_registrations_per_month->yAxis->min(0);
        $chart_registrations_per_month->yAxis->title(array('text'  => $this->get('translator')->trans('Registrations per month', array(), 'syw_front_main_stats_counter')));
        $chart_registrations_per_month->legend->enabled(true);
        $chart_registrations_per_month->plotOptions->area(array(
            'allowPointSelect'  => true,
            'dataLabels'    => array('enabled' => false),
            'showInLegend'  => true
        ));
        $chart_registrations_per_month->series($series);
        // end of chart

        $online = $this->getOnlineUsers();
        $actuallocale = $this->get('request')->getLocale();
        $transtolanguage = $this->get('doctrine')
            ->getRepository('SywFrontMainBundle:Languages')
            ->findOneBy(array('locale' => $actuallocale));
        $transform_array = $this->getTranslateForm();
        return array(
            'formTrans_navi' => $transform_array['navi']->createView(),
            'formTrans_route' => $transform_array['route']->createView(),
            'formTrans_footer' => $transform_array['footer']->createView(),
            'formTrans_others' => $transform_array['others']->createView(),
            'transtolanguage' => $transtolanguage->getLanguage(),
            'online' => $online,
            'metatitle' => $metatitle,
            'title1' => $title1,
            'title2' => $title2,
            'languages' => $languages,
            'stats' => $stats,
            'user' => $user,
            'chart' => $chart_registrations_per_month
        );
    }
}
