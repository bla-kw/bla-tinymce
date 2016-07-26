<?php

/*
 * ###_MODULE_###
 * Copyright (C) ###_YEAR_###  ###_COMPANY_###
 * info:  ###_EMAIL_###
 *
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation;
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
 *
 * ###_AUTHOR_###
 */

class blaTinyMceOxViewConfig extends blaTinyMceOxViewConfig_parent
{
    public function loadTinyMce()
    {
        $cfg = oxRegistry::getConfig();
        $blEnabled = in_array($this->getActiveClassName(), $cfg->getConfigParam("aTinyMCE_classes"));
        $blPlainCms = in_array($cfg->getActiveView()->getViewDataElement("edit")->oxcontents__oxloadid->value, $cfg->getConfigParam("aTinyMCE_plaincms"));
        $blFilemanager = $cfg->getConfigParam("blTinyMCE_filemanager");

        if (!$blEnabled) return;
        if ($blPlainCms) return oxRegistry::getLang()->translateString("BLA_TINYMCE_PLAINCMS");

        // processing editor config & other stuff
        $sLang = oxRegistry::getLang()->getLanguageAbbr(oxRegistry::getLang()->getTplLanguage());
        // array to assign shops lang abbreviations to lang file names of tinymce: shopLangAbbreviation => fileName (without .js )
        $aLang = array(
            "cs" => "cs",
            "da" => "da",
            "de" => "de",
            "fr" => "fr_FR",
            "it" => "it",
            "nl" => "nl",
            "ru" => "ru"
        );

        // default config
        $aDefaultConfig = array(
            'selector' => '"textarea:not(.mceNoEditor)"',
            'language' => '"' . (in_array($sLang, $aLang) ? $aLang[$sLang] : 'en') . '"',
            //'spellchecker_language'   => '"' . (in_array($sLang, $aLang) ? $aLang[$sLang] : 'en') . '"',
            'nowrap' => 'false',
            'entity_encoding' => '"raw"', // http://www.tinymce.com/wiki.php/Configuration:entity_encoding
            'height' => 300,
            'menubar' => 'false',
            'document_base_url' => '"' . $this->getBaseDir() . '"', // http://www.tinymce.com/wiki.php/Configuration:document_base_url
            'relative_urls' => 'false', // http://www.tinymce.com/wiki.php/Configuration:relative_urls
            'plugin_preview_width' => 'window.innerWidth',
            'plugin_preview_height' => 'window.innerHeight-90',
            'code_dialog_width' => 'window.innerWidth-50',
            'code_dialog_height' => 'window.innerHeight-130',
            'image_advtab' => 'true',
            'imagetools_toolbar' => '"rotateleft rotateright | flipv fliph | editimage imageoptions"',
            'moxiemanager_fullscreen' => 'true',
            'insertdatetime_formats' => '[ "%d.%m.%Y", "%H:%M" ]',
            'nonbreaking_force_tab' => 'true', // http://www.tinymce.com/wiki.php/Plugin:nonbreaking
            'autoresize_max_height' => '400',
            'external_filemanager_path' => '"../modules/bla/bla-tinymce/ResponsiveFilemanager/filemanager/"',
            'filemanager_access_key' => '"' . md5($_SERVER['HTTP_HOST']) . '"',
            'tinymcehelper' => '"' . $this->getSelfActionLink() . 'renderPartial=1"'
        );
        //merging with onfig override
        $aConfig = ($aOverrideConfig = $this->_getTinyCustConfig()) ? array_merge($aDefaultConfig, $aOverrideConfig) : $aDefaultConfig;


        // default plugins and their buttons
        $aDefaultPlugins = array(
            'advlist' => '', // '' = plugin has no buttons
            'anchor' => 'anchor',
            'autolink' => '',
            'autoresize' => '',
            'charmap' => 'charmap',
            'code' => 'code',
            'colorpicker' => '',
            'hr' => 'hr',
            'image' => 'image',
            'imagetools' => '',
            'insertdatetime' => 'insertdatetime',
            'link' => 'link',
            'lists' => '',
            'media' => 'media',
            'nonbreaking' => 'nonbreaking',
            'pagebreak' => 'pagebreak',
            'paste' => 'pastetext',
            'preview' => 'preview',
            'searchreplace' => 'searchreplace',
            'table' => 'table',
            'textcolor' => 'forecolor backcolor',
            'visualblocks' => '',
            //'visualchars'    => 'visualchars',
            'wordcount' => '',
            'oxfullscreen' => 'fullscreen', //custom fullscreen plugin
            //'oxgetseourl'    => 'yolo' //custom seo url plugin // wip
           'legacyoutput' => ''
        );
$aDefaultPlugins['image'] = false;
        // plugins for newsletter emails
        if ($this->getActiveClassName() == "newsletter_main") {
            $aDefaultPlugins["legacyoutput"] = false;
            $aDefaultPlugins["fullpage"] = "fullpage";
        }

        // override for active plugins
        $aOverridePlugins = $cfg->getConfigParam("aTinyMCE_plugins");
        $aPlugins = (empty($aOverridePlugins) || !is_array($aOverridePlugins)) ? $aDefaultPlugins : array_merge($aDefaultPlugins, $aOverridePlugins);
        $aPlugins = array_filter($aPlugins, function($value) { return $value !== false; });
              
        // array keys von $aPlugins enthalten aktive plugins
        $aConfig['plugins'] = '"' . implode(' ', array_keys($aPlugins)) . '"';

        // external plugins
        $aConfig['external_plugins'] = '{ "oxfullscreen":"' . $this->getModuleUrl('bla-tinymce', 'plugins/oxfullscreen/plugin.js') . '" ';
        if ($blFilemanager) $aConfig['external_plugins'] .= ',"roxy":"' . $this->getModuleUrl('bla-tinymce', 'plugins/roxy/plugin.js') . '" ';
        //$aConfig['external_plugins'] .= ',"oxgetseourl":"' . $this->getModuleUrl('bla-tinymce', 'plugins/oxgetseourl/plugin.js') . '" ';

        if ($aExtPlugins = $this->_getTinyExtPlugins()) {
            foreach ($aExtPlugins AS $plugin => $file) {
                $aConfig['external_plugins'] .= ', "' . $plugin . '": "' . $file . '" ';
            }
        }
        $aConfig['external_plugins'] .= ' }';
        
        // default toolbar buttons
        $aDefaultButtons = array(
            "undo redo",
            "bold italic underline strikethrough",
            "alignleft aligncenter alignright alignjustify",
            "bullist numlist",
            "outdent indent",
            "blockquote",
            "removeformat",
            "subscript",
            "superscript",
            "formatselect",
            "fontselect",
            "fontsizeselect",
            "subscript superscript"
        );
        $aOverrideButtons = oxRegistry::getConfig()->getConfigParam("aTinyMCE_buttons");
        $aButtons = (empty($aOverrideButtons) || !is_array($aOverrideButtons)) ? $aDefaultButtons : $aOverrideButtons ;
        
       // plugin buttons
        $aPluginButtons = array_filter($aPlugins);
   
       // zusätzliche buttons
        $aCustomButtons = $this->_getTinyToolbarControls();
       
        $aButtons = array_merge(array_filter($aButtons), array_filter($aPluginButtons), array_filter($aCustomButtons));
        $aConfig['toolbar'] = '"' . implode(" | ", $aButtons) . '"';


        // compile the whole config stuff
        $sConfig = '';
        foreach ($aConfig AS $param => $value) {
            $sConfig .= "$param: $value, ";
        }

        // add init script
        $sInit = 'tinymce.init({ ' . $sConfig . ' });';

        $sCopyLongDescFromTinyMCE = 'function copyLongDescFromTinyMCE(sIdent) 
{
    var editor = tinymce.get("editor_"+sIdent);
    if (editor && editor.isHidden() !== true)
    {
        console.log("copy content from tinymce");
        var content = editor.getContent().replace(/\[{([^\]]*?)}\]/g, function(m) { return m.replace(/&gt;/g, ">").replace(/&lt;/g, "<").replace(/&amp;/g, "&") });
        document.getElementsByName("editval[" + sIdent + "]").item(0).value = content;
        return true;
    }
    return false;
}

var origCopyLongDesc = copyLongDesc;
copyLongDesc = function(sIdent)
{
    if ( copyLongDescFromTinyMCE( sIdent ) ) return;
    console.log("tinymce disabled, copy content from regular textarea");
    origCopyLongDesc( sIdent );
}';

        // adding scripts to template
        $smarty = oxRegistry::get("oxUtilsView")->getSmarty();
        $sSufix = ($smarty->_tpl_vars["__oxid_include_dynamic"]) ? '_dynamic' : '';

        $aScript = (array)$cfg->getGlobalParameter('scripts' . $sSufix);
        $aScript[] = $sCopyLongDescFromTinyMCE;
        $aScript[] = $sInit;
        $cfg->setGlobalParameter('scripts' . $sSufix, $aScript);

        $aInclude = (array)$cfg->getGlobalParameter('includes' . $sSufix);
        $aInclude[3][] = $this->getModuleUrl('bla-tinymce', 'tinymce/tinymce.min.js');
        //$aInclude[3][] = 'https://code.jquery.com/jquery-2.2.4.min.js';
        $cfg->setGlobalParameter('includes' . $sSufix, $aInclude);

        return '<li style="margin-left: 50px;"><button style="border: 1px solid #0089EE; color: #0089EE;padding: 3px 10px; margin-top: -10px; background: white;" ' .
        'onclick="tinymce.each(tinymce.editors, function(editor) { if(editor.isHidden()) { editor.show(); } else { editor.hide(); } });"><span>' .
        oxRegistry::getLang()->translateString('BLA_TINYMCE_TOGGLE') . '</span></button></li>';
        // javascript:tinymce.execCommand(\'mceToggleEditor\',false,\'editor1\');
    }

    protected function _getTinyToolbarControls()
    {
        $aControls = (method_exists(get_parent_class(__CLASS__), __FUNCTION__)) ? parent::_getTinyToolbarControls() : array();
        return $aControls;
    }

    protected function _getTinyExtPlugins()
    {
        $aPlugins = oxRegistry::getConfig()->getConfigParam("aTinyMCE_external_plugins");
        if (method_exists(get_parent_class(__CLASS__), __FUNCTION__)) {
            $aPlugins = array_merge(parent::_getTinyExtPlugins(), $aPlugins);
        }
        return $aPlugins;
    }

    protected function _getTinyCustConfig()
    {
        $aConfig = oxRegistry::getConfig()->getConfigParam("aTinyMCE_config");
        if (method_exists(get_parent_class(__CLASS__), __FUNCTION__)) {
            $aConfig = array_merge(parent::_getTinyCustConfig(), $aConfig);
        }
        return $aConfig;
    }
}