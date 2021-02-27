<?php
/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

class Magpleasure_Adminlogger_Model_Observer_Mpblogcomment extends Magpleasure_Adminlogger_Model_Observer
{
    public function MpBlogCommentLoad($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpblogcomment')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogcomment::ACTION_COMMENT_LOAD,
            Mage::app()->getRequest()->getParam('id')
        );
    }

    public function MpBlogCommentSave($event)
    {
        $comment = $event->getObject();
        $log = $this->createLogRecord(
            $this->getActionGroup('mpblogcomment')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogcomment::ACTION_COMMENT_SAVE,
            $comment->getId()
        );
        if ($log){
            $log->addDetails(
                $this->_helper()->getCompare()->diff($comment->getData(), $comment->getOrigData())
            );
        }
    }

    public function MpBlogCommentDelete($event)
    {
        $this->createLogRecord(
            $this->getActionGroup('mpblogcomment')->getValue(),
            Magpleasure_Adminlogger_Model_Actiongroup_Mpblogcomment::ACTION_COMMENT_DELETE
        );
    }
}
