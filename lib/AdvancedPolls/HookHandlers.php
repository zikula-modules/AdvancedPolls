<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * AdvancedPolls Hooks Handlers.
 */
class AdvancedPolls_HookHandlers extends Zikula_Hook_AbstractHandler
{

    /**
     * Display hook for view.
     *
     * @param Zikula_Hook $hook The hook.
     *
     * @return void
     */
    public function uiView(Zikula_DisplayHook $hook)
    {
        // Input from the hook
        $callermodname = $hook->getCaller();
        $callerobjectid = $hook->getId();
        $areaId = $hook->getAreaId();

        if (empty($callerobjectid)) {
            return;
        }

        // Load module, otherwise translation is not working in template
        ModUtil::load('AdvancedPolls');

        // Check permissions
        if (!SecurityUtil::checkPermission('AdvancedPolls::', "::", ACCESS_READ)) {
            return;
        }
        
        // To do: Mechanism to obtain particular Poll, from $callermodname and $callerobjectid
        // For now get last active poll
        $items = ModUtil::apiFunc('AdvancedPolls', 'user', 'getall', array('desc' => true, 'numitems' => 1));
        $pollid = $items[0]['pollid'];

        // Get item
        $item = ModUtil::apiFunc('AdvancedPolls', 'user', 'get', array('pollid' => $pollid));

        // Security check
        if (SecurityUtil::checkPermission('AdvancedPolls::item', $item['title'] . "::" . $item['pollid'], ACCESS_READ)) {

            // is this poll currently open for voting
            $ispollopen = ModUtil::apiFunc('AdvancedPolls', 'user', 'isopen', array('item' => $item));
            // is this user/ip etc. allowed to vote under voting regulations
            $isvoteallowed = ModUtil::apiFunc('AdvancedPolls', 'user', 'isvoteallowed', array('item' => $item));
            $currentDate = new DateTime();
            if ($item['opendate'] > $currentDate) {
                $notyetopen = true;
            } else {
                $notyetopen = false;
            }

            // Now lets work out which view to display
            $displayvotingform = false;
            $displayresults = false;
            if ($results) { $displayresults = true; }
            if ($ispollopen && $isvoteallowed) { $displayvotingform = true;}
            if (!$isvoteallowed || (!$ispollopen && !$notyetopen)) { $displayresults = true; }
            if ($notyetopen) { $displaypreview = true; }

            if ($displayvotingform) {
                $template = 'user/votingform_short.tpl';
            } elseif ($displayresults) {
                $template = 'user/results_short.tpl';
            } elseif ($displaypreview) {
                $template = 'user/futurepoll_short.tpl';
            }
            
            $vars = ModUtil::getVar('AdvancedPolls');

            // Create the output object
            $view = Zikula_View::getInstance('AdvancedPolls', false, null, true);
            $view->assign('theme', UserUtil::getTheme());
            $view->assign($vars);
            $view->assign('lang', ZLanguage::getLanguageCode());
            $view->assign('displayvotingform', $displayvotingform);
            $view->assign('displayresults', $displayresults);
            $view->assign('displaypreview', $displaypreview);
            $view->assign('areaid', $areaId);
            $view->assign('item', $item);
            $view->assign('ispollopen', $ispollopen);
            $view->assign('polltype', $item['multipleselect']);
            $view->assign('multiplecount', $item['multiplecount']);
            //$view->assign('scalingfactor', ModUtil::getVar('AdvancedPolls', 'scalingfactor'));
            $view->assign('pollid', $pollid);
            $view->assign('hookcall', true);

            $response = new Zikula_Response_DisplayHook('provider.advancedpolls.ui_hooks.poll', $view, $template);
            $hook->setResponse($response);
        }
    }
}
