<?php

namespace Kdyby\Doctrine\Mapping;



interface IFormMapper
{

    function toArray($entity);

	function toEntity($array, $entity);

}
