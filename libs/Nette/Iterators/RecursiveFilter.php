<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @package Nette\Iterators
 */



/**
 * Callback recursive iterator filter.
 *
 * @author     David Grudl
 * @package Nette\Iterators
 */
class NNRecursiveCallbackFilterIterator extends FilterIterator implements RecursiveIterator
{
	/** @var callback */
	private $callback;

	/** @var callback */
	private $childrenCallback;


	/**
	 * Constructs a filter around another iterator.
	 * @param
	 * @param  callback
	 */
	public function __construct(RecursiveIterator $iterator, $callback, $childrenCallback = NULL)
	{
		parent::__construct($iterator);
		$this->callback = $callback;
		$this->childrenCallback = $childrenCallback;
	}



	public function accept()
	{
		return $this->callback === NULL || call_user_func($this->callback, $this);
	}



	public function hasChildren()
	{
		return $this->getInnerIterator()->hasChildren()
			&& ($this->childrenCallback === NULL || call_user_func($this->childrenCallback, $this));
	}



	public function getChildren()
	{
		return new self($this->getInnerIterator()->getChildren(), $this->callback, $this->childrenCallback);
	}

}
