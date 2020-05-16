<?php

class Twitter_Form_Decorator_Errors extends Zend_Form_Decorator_Errors
{
	public function render($content)
	{
		$element = $this->getElement();
		$view = $element->getView();

		if (null === $view) {
				return $content;
		}

		$errors = $element->getMessages();
		if (empty($errors)) {
				return $content;
		}

		$element->setAttrib("class", trim("error " . $element->getAttrib("class")));

		$wrapper = $element->getDecorator("outerwrapper");
		if($wrapper)
		{
			$wrapper->setOption("class", trim("error " . $wrapper->getOption("class")));
		}

		$separator = $this->getSeparator();
		$placement = $this->getPlacement();
		$errorHtml = "";
		foreach($errors as $currentError)
		{
			$errorHtml .= '<span class="help-block">'.$currentError.'</span>';
		}
		//$errors    = $view->formErrors($errors, $this->getOptions());

		switch ($placement) {
				case self::APPEND:
						return $content . $separator . $errorHtml;
				case self::PREPEND:
						return $errorHtml . $separator . $content;
		}
	}
}
