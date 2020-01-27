<?php

namespace PHPSTORM_META {

	override(\Doctrine\ODM\MongoDB\DocumentManager::find(0), map([
		'' => '@',
	]));
	override(\Doctrine\ODM\MongoDB\DocumentManager::getRepository(0), map([
		'' => '@',
	]));
	override(\Doctrine\ODM\MongoDB\DocumentManager::getReference(0), map([
		'' => '@',
	]));
	override(\Doctrine\Persistence\ObjectManager::getRepository(0), map([
		'' => '@',
	]));
}
