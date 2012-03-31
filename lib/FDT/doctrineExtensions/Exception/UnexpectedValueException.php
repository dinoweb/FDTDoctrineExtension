<?php

namespace FDT\doctrineExtensions\Exception;

use FDT\doctrineExtensions\Exception;

/**
 * UnexpectedValueException
 * 
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Exception
 * @subpackage UnexpectedValueException
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class UnexpectedValueException 
    extends \UnexpectedValueException
    implements Exception
{}