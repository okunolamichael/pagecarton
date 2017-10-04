<?php
/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @category   PageCarton CMS
 * @package    Ayoola_Object_CreateFile
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: CreateFile.php 4.11.2012 6.16pm ayoola $
 */

/**
 * @see 
 */
 
//require_once 'Ayoola/Dbase/Table/Abstract/Xml.php';


/**
 * @category   PageCarton CMS
 * @package    Ayoola_Object_CreateFile
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class Ayoola_Object_CreateFile extends Ayoola_Object_Abstract
{
	
    /**
     * The method does the whole Class Process
     * 
     */
	protected function init()
    {
	//	var_export( __LINE__ );
        if( ! @$_REQUEST['file_type'] )
        {
       //     return false;
        }

        switch( @$_REQUEST['file_type'] )
        {
            case 'table':
                $type = 'Database Table Class';
            break;
            default:
                $type = 'Widget Class';
            break;
        }
		$this->createForm( 'Continue', 'Create a new ' . $type . ' file' );
		$this->setViewContent( $this->getForm()->view(), true );
		if( ! $values = $this->getForm()->getValues() ){ return false; } 

        if( Ayoola_Loader::loadClass( $values['class_name'] ) )
        {
            $this->getForm()->setBadnews( 'Class already exist, choose another class name' );
			$this->setViewContent( $this->getForm()->view(), true );
            return false;
        }

        $filter = new Ayoola_Filter_ClassToFilename();
        $filename = $filter->filter( $values['class_name'] );
        $dir = Ayoola_Application::getDomainSettings( APPLICATION_PATH ) . DS . 'modules' ;
        $module = array_shift( explode( '_', $values['class_name'] ) );

        switch( $module )
        {
            case 'Ayoola':
            case 'PageCarton':
              $dir = LIBRARY_PATH;
            break;
        }
        $path = $dir.  DS . $filename; 
   //     var_export( $path );
   //     exit();
 //       $this->setViewContent( $path );
 //      $this->setViewContent( var_export( $values, true ) );
     //   var_export( $path );
        $search = array();
        $search['{date}'] = date('l jS \of F Y h:iA');
        $search['{year}'] = date('Y');
        $search['{filename}'] = basename( $path );
        $search['{username}'] = Ayoola_Application::getUserInfo( 'email' );
        switch( @$_REQUEST['file_type'] )
        {
            case 'table':
                $sampleFile = 'PageCarton_Table_Sample';
                $search["'{datatypes}'"] = var_export( array_combine( $values['fields'], $values['datatypes'] ), true );
            break;
            default:
                $sampleFile = 'PageCarton_Widget_Sample_Blank';
                $search['{widget_title}'] = $values['widget_title'];
            break;
        }
        $search[$sampleFile] = $values['class_name'];

        $sampleFile = $filter->filter( $sampleFile );
//      $this->setViewContent( $sampleFile );
        $sampleFile = Ayoola_Loader::checkFile( $sampleFile );  
        $content = file_get_contents( $sampleFile );
        $content = str_ireplace( array_keys( $search ), array_values( $search ), $content );
        Ayoola_Doc::createDirectory( dirname( $path  ) );
        file_put_contents( $path, $content );

    //    $this->setViewContent( '<textarea>' . $content . '</textarea>' );
        $this->setViewContent( '<h1 class="goodnews">File created successfully</h1>', true ); 
        $this->setViewContent( '<p>Customize this ' . $type . ' (' . $values['class_name'] . ') by editing the file below:</p>' ); 
        $this->setViewContent( '<p style="font-size:smaller;">' . $path . '</p>' ); 
 		
    } 
	
	//	This is to implement the abstract method of the parent class. Not all inheriting classes needs a form
	public function createForm( $submitValue, $legend = null, Array $values = null )
	{
        $form = new Ayoola_Form( 'name=>' . $this->getObjectName() );

		$fieldset = new Ayoola_Form_Element;

		$fieldset->addElement( array( 'name' => 'class_name', 'label' => 'PHP Class Name', 'placeholder' => 'e.g. Sample_Class_Name', 'type' => 'InputText', 'value' => @$values['class_name'] ) );

        $fieldset->addRequirement( 'class_name', array( 'WordCount' => array( 3,100 ), 'CharacterWhitelist' => array( 'badnews' => 'The allowed characters are lower case alphabets (a-z), upper case alphabets (A-Z) and underscore (_).', 'character_list' => '^a-z_A-Z', ) ) );

 
		$fieldset->addLegend( $legend );
		$form->addFieldset( $fieldset );	

        switch( @$_REQUEST['file_type'] )
        {
            case 'table':

                $i = 0;
                $values['fields'] = $values['fields'] ? : Ayoola_Form::getGlobalValue( 'fields' );
                $values['datatypes'] = $values['datatypes'] ? : Ayoola_Form::getGlobalValue( 'datatypes' );
   //             var_export(  $values );
                do
                {
                    $fieldsetX = new Ayoola_Form_Element; 
                    $fieldsetX->hashElementName = false;
                    $fieldsetX->duplicationData = array( 'add' => 'New Field', 'remove' => 'Remove Field', 'counter' => 'field_counter', );
        //        var_export(  $values['field'][$i] );
        //        var_export(  $values['datatype'][$i] );

                    $fieldsetX->container = 'div';
                    $form->wrapForm = false;
                    $fieldsetX->addElement( array( 'name' => 'fields', 'style' => 'max-width: 40%;', 'label' => '', 'placeholder' => 'Field name e.g. full_name', 'type' => 'InputText', 'multiple' => 'multiple', 'value' => @$values['fields'][$i] ) );

                    $options = array( 
                                        'INPUTTEXT' => 'TEXT',
                                        'INT' => 'INTEGER',
                                        'JSON' => 'ARRAY & OBJECTS',
                                        'INPUTTEXT, UNIQUE' => 'UNIQUE TEXT',
                                         );
                    $fieldsetX->addElement( array( 'name' => 'datatypes', 'style' => 'max-width: 40%;', 'label' => '', 'placeholder' => 'Data Type', 'type' => 'Select', 'multiple' => 'multiple', 'value' => @htmlspecialchars( $values['datatypes'][$i] ) ), $options );
                    $fieldsetX->allowDuplication = true;  
                    $fieldsetX->placeholderInPlaceOfLabel = true;
               //     $fieldsetX->wrapper = 'white-content-theme-border';  
                    $i++;
                    $fieldsetX->addLegend( 'Database Table Fields #<span name="field_counter">' . $i .  '</span>' );
                    $form->oneFieldSetAtATime = false;   
                    $form->addFieldset( $fieldsetX );
                }
                while( ! empty( $values['fields'][$i] ) || ! empty( $values['datatypes'][$i] ) );

            break;
            default:
                $fieldset = new Ayoola_Form_Element; 

	        	$fieldset->addElement( array( 'name' => 'widget_title', 'label' => 'Display Name', 'placeholder' => 'Widget Title', 'type' => 'InputText', 'value' => @$values['widget_title'] ) );
                $fieldset->addRequirement( 'widget_title', array( 'WordCount' => array( 3,100 ), 'CharacterWhitelist' => array( 'badnews' => 'The allowed characters are lower case alphabets (a-z), upper case alphabets (A-Z), space and underscore (_).', 'character_list' => '^0-9\sa-z_A-Z', ) ) );
                $form->addFieldset( $fieldset );
            break;
            
        }

		$form->submitValue = $submitValue;
		$this->setForm( $form );
	}
	// END OF CLASS
}