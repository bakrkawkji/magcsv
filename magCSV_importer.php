<?php
/*
*  MagCSV attributes & attribute sets importer
*  v 0.0.3 / Nov.2013 
*  bakr.kawkji@coalitiontechnologies.com
*
*  #################################################### DISCLAIMER #######################################################################
*  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
*  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
*  IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
*  ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
* 
*  MAKE SURE TO BACKUP YOUR MAGENTO SYSTEM/DATABSE BEFORE EXECUTING THIS SCRIPT
*  WE TAKE NO RESPONSIBILITY FOR ANY HARM THAT THIS SCRIPT MAY CAUSE TO YOUR SITE/INSTALLATION, NEVER RUN IN LIVE ENVIRONMENTS.
*  IF YOU ARE NOT SURE WHAT THIS SCRIPT DOES, PLEASE DON'T EXECUTE IT.
*  #################################################### DISCLAIMER #######################################################################
*
*  #### Prerequesties:
*  SimpleExcel: http://faisalman.github.io/simple-excel-php/api/0.3/
*
*  #### Usage:
*
*  - Get SimpleExcel and upload it to your Magenot root folder
*  - Upload this script to your Magento root folder
*  - Update the configuration variables @configuration
*  - Set what you want to do with imported files in the $do variable
*  - You may want to set custom settings or add variables to the $args array
*  - Visit this script file from the browser to execute it, the import process log will be displayed for you
*
*
*  Inspired by:
*  http://inchoo.net/ecommerce/magento/programatically-create-attribute-in-magento-useful-for-the-on-the-fly-import-system/
*  http://blog.onlinebizsoft.com/magento-programmatically-insert-new-attribute-option/
*  http://mediotype.com/commerceacademy/blog/build-attribute-set-magento/
*
*/
?>
<?php
	/*
	* @configuration
	*/
	$magento_path = 'app/Mage.php';
	$simpleExcel_path = 'SimpleExcel/SimpleExcel.php';
	
	$attributesCsvFile = 	 'var/import/YOUR_ATTRIBUTES_FILE.csv';  		   // system path to your attributes file, ex: 'var/import/MagCSV-attributes-20131110082100-6428.csv'
	$attributesSetsCsvFile = 'var/import/YOUR_ATTRIBUTES_SETS_FILE.csv';  // system path to your attributes sets file
	
	$do = array(
		'CreateAttibutes' => true,			// set false to turn off attributes creation
		'AddAttributeOptions' => true,		// set false to turn off adding attributes options
		'CreateAttributesSets' => true,     // set false to turn off attributes sets creation
		'AddAttrSetAttribute' => true       // set false to turn off adding attributes options
	);
	
	/*
	* @configuration
	*/
?>
<!doctype html>
<html>
<head>
	<style type="text/css">
		@import url(//fonts.googleapis.com/css?family=Raleway:100,300,700,900,500);
		html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{border:0;font-size:100%;font:inherit;vertical-align:baseline;margin:0;padding:0}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:none}table{border-collapse:collapse;border-spacing:0}
		body {
		  font-family: Raleway, Helvetica, Arial, sans-serif;
		  font-size: 14px;
		  line-height: 1.428571429;
		  color: #333333;
		  background-color: #ffffff;
		}
		.wrap{
			position:relative;
			margin:40px auto;
			width: 970px;
		}
		.jumbotron {
			background-color: #7A62D3;
			color: #FFFFFF;
			font-size: 21px;
			font-weight: 200;
			line-height: 2.14286;
			margin-bottom: 30px;
			padding: 30px;
			text-align: center;
		}
		.jumbotron p{
			font-size:40px;
			line-height:40px;
		}
		h1 {
			font-size: 60px;
			font-weight: 300;
			line-height: 60px;
			margin-bottom: 20px;
		}
		h1 small{
			font-size:20px;
		}
		strong{
			font-weight:bold;
			color:#000;
		}
		.success{
			color:#379102;
		}
		.warning{
			color:#2B7680;
		}
		.error{
			color:#EE0000;
		}
	</style>
</head>
<body>
<div class="wrap">
<div class="jumbotron">
	<h1>MagCSV importer <small>v 0.0.3</small></h1>
	<p>Import status</p>
</div>
<?php

require_once $magento_path;
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

//========= functions =========
function createAttr($code, $label, $attribute_type, $product_type){

    $args = array(
        'attribute_code' => cleanString($code),
        'frontend_input' => $attribute_type,
        'frontend_label' => $label,
        'default_value_text' => '',
        'is_global' => '1',
        'default_value_yesno' => '0',
        'default_value_date' => '',
        'is_unique' => '0',
		'is_filterable_in_search' => '0',
        'default_value_textarea' => '',
        'is_required' => '0',
        'apply_to' => '',
        'is_visible_in_advanced_search' => '0',
        'is_configurable' => '1',
        'is_searchable' => '0',
        'is_used_for_price_rules' => '0',
        'is_comparable' => '0',
		'is_filterable' => '0',
        'is_html_allowed_on_front' => '1',
        'is_wysiwyg_enabled' => '0',
        'is_visible_on_front' => '1',
        'used_for_sort_by' => '0',
        'used_in_product_listing' => '0'
    );
	
    $catalog = Mage::getModel('catalog/resource_eav_attribute');
	
	if ($catalog->getDefaultValueByInput($attribute_type)) {
        $args['default_value'] = $this->getRequest()->getParam($catalog->getDefaultValueByInput($attribute_type));
    }
	
    if ( $catalog->getIsUserDefined() != 0 || is_null($catalog->getIsUserDefined()) ) {
        $args['backend_type'] = $catalog->getBackendTypeByInput($attribute_type);
    }
	
    $catalog->addData($args);
    $catalog->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
    $catalog->setIsUserDefined(1);
	
	if(!attributeExists(cleanString($code))){
		try {
			$save = $catalog->save();
		}
		catch(Exception $e){
			echo $e;
		}
		
		if($save){
			echo "<span class='success'>Attribute with code: <strong>".$label."</strong> has been created successfully</span></br>";
		}
	}
	else echo "<span class='warning'>Attribute with code: <strong>".$label."</strong> already exist</span></br>";
}

function addAttrValue($arg_attribute, $arg_value){
	$attr = cleanString($arg_attribute);
	$attribute_model = Mage::getModel('eav/entity_attribute');

	$attribute_code = $attribute_model->getIdByCode('catalog_product', $attr);
	$attribute = $attribute_model->load($attribute_code);

	if(attributeExists($attr) && !attrValueExists($attr, $arg_value)){
		$value['option'] = array($arg_value,$arg_value);
		$result = array('value' => $value);
		$attribute->setData('option',$result);
		$attribute->save();
		echo "<span class='success'>Attribute ".$attr." option: <strong>".$arg_value."</strong> has been added successfully</span></br>";
	}
	elseif(attributeExists($attr) && attrValueExists($attr, $arg_value)){
		echo "<span class='warning'>Attribute ".$attr." with option: <strong>".$arg_value."</strong> already exist</span></br>";
	}
	else{
		echo "<span class='error'>Attribute with code: <strong>".$attr."</strong> don't exist</span></br>";
	}

   return false;
}

function attrValueExists($arg_attribute, $arg_value){
	$attribute_model = Mage::getModel('eav/entity_attribute');
	$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
	$attribute_code = $attribute_model->getIdByCode('catalog_product', cleanString($arg_attribute));
	$attribute = $attribute_model->load($attribute_code);
	$attribute_table = $attribute_options_model->setAttribute($attribute);
	$options = $attribute_options_model->getAllOptions(false);

	foreach($options as $option)
	{
		if ($option['label'] == $arg_value)
		{	
			return $option['value'];
		}
	}
	return false;
}

function attributeExists($arg_attribute){
	$attribute_model = Mage::getModel('eav/entity_attribute');
	$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
	$attribute_code = $attribute_model->getIdByCode('catalog_product', cleanString($arg_attribute));
	if($attribute_code){
		return true;
	}
	else
	return false;
}

/**
* @param $data array('code' => 'new_set_code', 'name' => 'new_set_name')
*/
function createAttrSet($data){
	$entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
	$newAttributeSet = Mage::getModel('eav/entity_attribute_set');
	$newAttributeSet->setEntityTypeId($entityTypeID);
	$newAttributeSet->addData($data);
	$newAttributeSet->setAttributeSetName($data['name']);
	try{
	$newAttributeSet->validate();
	} catch (Exception $e) {
		echo "<span class='warning'>Attribute set with code: <strong>".$data['code']."</strong> already exists</span></br>";
	}

	try{
		$newAttributeSet->save();
	} catch (Exception $e) {
		
	}

	try{
		$templateAttributeSet = _getDefaultAttributeSet();
		$newAttributeSet->initFromSkeleton($templateAttributeSet->getId());
		$newAttributeSet->save();
		echo "<span class='success'>Attribute set with code: <strong>".$data['code']."</strong> has been created successfully</span></br>";
		return $newAttributeSet;
	} catch (Exception $e) {
		
	}
	
	return NULL;
}

function _getDefaultAttributeSet(){
	$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
	$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
		->setEntityTypeFilter($entityTypeId)
		->addFilter('attribute_set_name', 'Default');

	if ($attributeSetCollection->count() > 0) {
		$response = $attributeSetCollection->getFirstItem();
	} else {
		$response = FALSE;
	}
	return $response;
}

function addAttrSetAttribute($attrCode, $attrSet){
	
	$attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code','catalog_product')->getFirstItem();
	$attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection();
	$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter(cleanString($attrCode))->getFirstItem();
	$attCode = $attributeInfo->getAttributeCode();
	$attId = $attributeInfo->getId();
	
	if($attId){
	
		$catalog = Mage::getModel('eav/entity_setup','core_setup');
		$attrsetID = $catalog->getAttributeSetId('catalog_product',$attrSet);
		$set = Mage::getModel('eav/entity_attribute_set')->load($attrsetID);
		$group = Mage::getModel('eav/entity_attribute_group')->getCollection()->addFieldToFilter('attribute_set_id',$attrsetID)->addFieldToFilter('attribute_group_name', 'General')->setOrder('attribute_group_id',"ASC")->getFirstItem();
		$groupId = $group->getId();
		$newItem = Mage::getModel('eav/entity_attribute');
		$newItem->setEntityTypeId($attSet->getId())
				  ->setAttributeSetId($attrsetID)
				  ->setAttributeGroupId($groupId)
				  ->setAttributeId($attId)
				  ->setSortOrder(999) // Sort Order, high number will add the attribute to the end of the group
				  ->save();
				  
		// @todo: check if the attriute is already added to this attribute set to show more proper status
		echo "<span class='success'>Attribute with code: <strong>".$attrCode."</strong> has been added to attribute set <strong>".$attrSet."</strong> successfully under group <strong>General</strong></span></br>";
	} else{
		echo "<span class='warning'>Attribute with code: <strong>".$attrCode."</strong> cannot be added to attribute set <strong>".$attrSet."</strong>, Attribute don't exist</span></br>";
	}
}

// has the same output as in the templates produced by the magCSV generator
function cleanString($string) {
   $string = str_replace(' ', '_', $string);
   return strtolower(preg_replace('/[^A-Za-z0-9_\-]/', '', $string));
}

use SimpleExcel\SimpleExcel;
require_once $simpleExcel_path;

function doCreateAttibutes($fileNamePath){

	$reader = new SimpleExcel('csv');
	try{
		$reader->parser->loadFile($fileNamePath);
		
		$attributes_count =  count($reader->parser->getField());

		for($i=2;$i<=$attributes_count;$i++){
			$row = $reader->parser->getRow($i);
			createAttr(cleanString($row[0]), $row[0], $row[2], $row[3]);
		}
		
	} catch (Exception $e) {
        echo '<span class="error">Couldn\'t open the file at:'.$fileNamePath.'  make sure the file do exist</span><br />';
    }

}

function doAddAttributeOptions($fileNamePath){

	$reader = new SimpleExcel('csv');
	try{
		$reader->parser->loadFile($fileNamePath);
		
		$attributes_count = count($reader->parser->getField());
		
		for($i=2;$i<=$attributes_count;$i++){
			$row = $reader->parser->getRow($i);
			$attribute_options = explode(',', $row[1]);
			
			foreach($attribute_options as $attrId => $attrVal){
				addAttrValue($row[0], $attrVal);
			}
		}
		
	} catch (Exception $e) {
        echo '<span class="error">Couldn\'t open the file at:'.$fileNamePath.'  make sure the file do exist</span><br />';
    }
}

function doCreateAttributesSets($fileNamePath){

	$reader = new SimpleExcel('csv');
	try{
		$reader->parser->loadFile($fileNamePath);
		
		$attributesSets_count =  count($reader->parser->getField());

		for($i=2;$i<=$attributesSets_count;$i++){
			$row = $reader->parser->getRow($i);
			$attr = array(
				'code' => cleanString($row[0]),
				'name' => $row[0]
			);
			createAttrSet($attr);
		}
	} catch (Exception $e) {
        echo '<span class="error">Couldn\'t open the file at:'.$fileNamePath.'  make sure the file do exist</span><br />';
    }

		
}

function doAddAttrSetAttribute($fileNamePath){

	$reader = new SimpleExcel('csv');
	try{
		$reader->parser->loadFile($fileNamePath);
		
		$attributesSets_count =  count($reader->parser->getField());

		for($i=2;$i<=$attributesSets_count;$i++){
			$row = $reader->parser->getRow($i);
			$attributes = explode(',', $row[1]);
			
			foreach($attributes as $attrId => $attrCode){
				addAttrSetAttribute($attrCode, $row[0]);
				//echo  $row[0].' '.$attrCode.' ';
			}
		}
	} catch (Exception $e) {
        echo '<span class="error">Couldn\'t open the file at:'.$fileNamePath.'  make sure the file do exist</span><br />';
    }	
}
//========= functions =========


// CREATE ATTRIBUTES FIRST, THEN ADD ATTRIBUTE OPTIONS, THEN CREATE ATTRIBUTE SETS

if($do['CreateAttibutes']){
	doCreateAttibutes($attributesCsvFile);
}
if($do['AddAttributeOptions']){
	doAddAttributeOptions($attributesCsvFile);
}
if($do['CreateAttributesSets']){
	doCreateAttributesSets($attributesSetsCsvFile);
}
if($do['AddAttrSetAttribute']){
	doAddAttrSetAttribute($attributesSetsCsvFile);
}

?>
</div>
</body>
</html>