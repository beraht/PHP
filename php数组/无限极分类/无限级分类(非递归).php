<?php

	/**
	 * @param 需要传递一个数组,该数组是一个无规律,但是存在pid(存在上下级之分的数据)
	 * @return 返回的是一个处理过后额数据
	 */

	public function getdata($menus){
		$tree = array();  
		//第一步，将分类id作为数组key,并创建children单元  
		foreach($menus as $category){  
		    $tree[$category['mid']] = $category;  
		    $tree[$category['mid']]['children'] = array();  
		}  
		//第二步，利用引用，将每个分类添加到父类children数组中，这样一次遍历即可形成树形结构。  
		foreach($tree as $key=>$item){  
		    if($item['pid'] != 0){  
		        $tree[$item['pid']]['children'][] = &$tree[$key];//注意：此处必须传引用否则结果不对  
		        if($tree[$key]['children'] == null){  
		            unset($tree[$key]['children']); //如果children为空，则删除该children元素（可选）  
		        }  
		    }  
		}  
		//第三步，删除无用的非根节点数据  
		foreach($tree as $key=>$category){  
		    if($category['pid'] != 0){  
		        unset($tree[$key]);  
		    }  
		}  
		  
		return $tree;	

	}