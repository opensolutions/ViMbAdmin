<?php

class Twitter_Form_Decorator_Checkboxlabel extends Zend_Form_Decorator_HtmlTag
{
	public function render($content)
	{
		$element = $this->getElement();
		$separator = $this->getSeparator();

		return $content . $separator . $element->getLabel();
	}
}
