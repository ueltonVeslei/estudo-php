<?php
class Netreviews_Avisverifies_Model_RichsnippetsList
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label'=> 'No rich-snippets'),
            array('value' => 'schema', 'label'=> 'rich-snippets using schema.org format'),
            array('value' => 'rdfa', 'label'=> 'rich-snippets using RDFa format'),
        );
    }
}