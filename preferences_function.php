<?php
/**
 ***********************************************************************************************
 * Verarbeiten der Einstellungen des Admidio-Plugins FormFiller
 *
 * @copyright 2004-2019 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *
 * Parameters:
 *
 * mode     : 1 - Save preferences
 *            2 - show  dialog for deinstallation
 *            3 - deinstall
 * form         - The name of the form preferences that were submitted.
 *
 ***********************************************************************************************
 */

require_once(__DIR__ . '/../../adm_program/system/common.php');
require_once(__DIR__ . '/common_function.php');
require_once(__DIR__ . '/classes/configtable.php');

// only authorized user are allowed to start this module
if (!$gCurrentUser->isAdministrator())
{
	$gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

$pPreferences = new ConfigTablePFF();
$pPreferences->read();

// Initialize and check the parameters
$getMode = admFuncVariableIsValid($_GET, 'mode', 'numeric', array('defaultValue' => 1));
$getForm = admFuncVariableIsValid($_GET, 'form', 'string');

// in ajax mode only return simple text on error
if ($getMode == 1)
{
    $gMessage->showHtmlTextOnly(true);
}

switch ($getMode)
{
	case 1:
	
		try
		{
			switch ($getForm)
    		{            	
            	case 'configurations':
            	
					unset($pPreferences->config['Formular']);
				
    				for ($conf = 0; isset($_POST['desc'. $conf]); $conf++)
    				{  				
        				$pPreferences->config['Formular']['desc'][] = $_POST['desc'. $conf];
    					$pPreferences->config['Formular']['font'][] = $_POST['font'. $conf];
    					$pPreferences->config['Formular']['style'][] = $_POST['style'. $conf];
    					$pPreferences->config['Formular']['size'][] = $_POST['size'. $conf];
    					$pPreferences->config['Formular']['color'][] = $_POST['color'. $conf];
    					$pPreferences->config['Formular']['labels'][] = $_POST['labels'. $conf];
    					$pPreferences->config['Formular']['pdfform_orientation'][] = $_POST['pdfform_orientation'. $conf];
    					$pPreferences->config['Formular']['pdfform_size'][] = $_POST['pdfform_size'. $conf];
    					$pPreferences->config['Formular']['pdfform_unit'][] = $_POST['pdfform_unit'. $conf];
    					$pPreferences->config['Formular']['pdfid'][] = (isset($_POST['pdfid'. $conf]) ? $_POST['pdfid'. $conf] : 0);
    					$pPreferences->config['Formular']['relation'][] = (isset($_POST['relationtype_id'. $conf]) ? $_POST['relationtype_id'. $conf] : '');
    				
    					$allColumnsEmpty = true;

    					$fields = array();
    					$positions = array();
    					for ($number = 1; isset($_POST['column'.$conf.'_'.$number]); $number++)
    					{
        					if (strlen($_POST['column'.$conf.'_'.$number]) > 0 && strlen($_POST['position'.$conf.'_'.$number]) > 0)
        					{
        						$allColumnsEmpty = false;
            					$fields[] = $_POST['column'.$conf.'_'.$number];
            					$positions[] = $_POST['position'.$conf.'_'.$number];
        					}
    					}
    			
    					if ($allColumnsEmpty)
    					{
    						$gMessage->show($gL10n->get('PLG_FORMFILLER_ERROR_MIN_DATA'));
    					}
    					$pPreferences->config['Formular']['fields'][] = $fields;	
    					$pPreferences->config['Formular']['positions'][] = $positions;		
    				}
            		break;
            	
        		case 'options':
        		
        			$pPreferences->config['Optionen']['maxpdfview'] = $_POST['maxpdfview'];
        			$pPreferences->config['Optionen']['pdfform_addsizes'] = $_POST['pdfform_addsizes'];
            		break; 
            
        		default:
           			$gMessage->show($gL10n->get('SYS_INVALID_PAGE_VIEW'));
    		}
		}
		catch (AdmException $e)
		{
			$e->showText();
		}    
    
		$pPreferences->save();
		echo 'success';
		break;

	case 2:
	
		$headline = $gL10n->get('PLG_FORMFILLER_DEINSTALLATION');
	 
		// create html page object
   	 	$page = new HtmlPage($headline);
    
    	// add current url to navigation stack
    	$gNavigation->addUrl(CURRENT_URL, $headline);

    	// create module menu with back link
    	$organizationNewMenu = new HtmlNavbar('menu_deinstallation', $headline, $page);
   	 	$organizationNewMenu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back.png');
    	$page->addHtml($organizationNewMenu->show(false));
    
    	$page->addHtml('<p class="lead">'.$gL10n->get('PLG_FORMFILLER_DEINSTALLATION_FORM_DESC').'</p>');

    	// show form
    	$form = new HtmlForm('deinstallation_form', ADMIDIO_URL . FOLDER_PLUGINS . PLUGIN_FOLDER .'/preferences_function.php?mode=3', $page);
    	$radioButtonEntries = array('0' => $gL10n->get('PLG_FORMFILLER_DEINST_ACTORGONLY'), '1' => $gL10n->get('PLG_FORMFILLER_DEINST_ALLORG') );
   	 	$form->addRadioButton('deinst_org_select',$gL10n->get('PLG_FORMFILLER_ORG_CHOICE'),$radioButtonEntries);    
    	$form->addSubmitButton('btn_deinstall', $gL10n->get('PLG_FORMFILLER_DEINSTALLATION'), array('icon' => THEME_URL .'/icons/delete.png', 'class' => ' col-sm-offset-3'));
    
    	// add form to html page and show page
    	$page->addHtml($form->show(false));
    	$page->show();
    	break;
    
	case 3:
    
		$gNavigation->addUrl(CURRENT_URL);
		$gMessage->setForwardUrl($gHomepage);		

		$gMessage->show($gL10n->get('PLG_FORMFILLER_DEINST_STARTMESSAGE').$pPreferences->delete($_POST['deinst_org_select']) );
   		break;
}
