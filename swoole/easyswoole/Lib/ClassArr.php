<?php
namespace App\Lib;

/**
 * 做一些反射机制有关的 处理
 */
class ClassArr {

	/**
	 * 要管理的类(命名空间下的类)
	 * @auth   singwa
	 * @date   2018-10-21T11:23:12+0800
	 * @return [type]                   [description]
	 */
	public function uploadClassStat() {
		return [
			"image" => "\App\Lib\Upload\Image",
			"video" => "\App\Lib\Upload\Video",
		];
	}

	/**
	 * [initClass description]
	 * @auth   singwa
	 * @date   2018-10-21T11:28:05+0800
	 * @param  [type]                   $type           代表的是uploadClassStat中的 key 如image ,video
	 * @param  [type]                   $supportedClass 代表的是uploadClassStat中整个数组
	 * @param  array                    $params         实例化时 __construct 需要传递的参数
	 * @param  boolean                  $needInstance   是否需要实例化
	 * @return [type]                                   [description]
	 */
	public function initClass($type, $supportedClass, $params = [], $needInstance = true) {
		if(!array_key_exists($type, $supportedClass)) {
			return false;
		}
        //某个具体的类 如:"\App\Lib\Upload\Image",
		$className = $supportedClass[$type];
								//建立反射类				       //实例化 类       //传递的参数
		return $needInstance ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;
	}

}