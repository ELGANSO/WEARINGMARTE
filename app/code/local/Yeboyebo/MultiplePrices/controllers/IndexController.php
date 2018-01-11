<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 9/01/18
 * Time: 12:32
 */
class Yeboyebo_MultiplePrices_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout ();
		$this->renderLayout ();
	}

	public function mamethodeAction()
	{
		echo 'test mamethode';
	}
}