<?php
/**
                **PHP二维数组，多参数去重
                **$arr 原始数组
                **$filter 条件，多条件传数组
                **return new去重后数组
                */
                function array_unique_fb($arr=array(),$filter){   
                    $res = array();      
                    foreach ($arr as $key => $value) {
                        $newkey='';
                        if (is_array($filter)) {
                            foreach ($filter as $fv) {
                                $newkey.=$value[$fv];
                            }
                        }else{
                            $newkey=$value[$filter];
                        }
                        foreach ($value as $vk => $va) {
                            if (isset($res[$newkey])) {
                                $res[$newkey][$vk]=$va;
                            }else{
                                $res[$newkey][$vk]=$va;
                            }
                        }
                    }
                    return $res;
                }

                $tem=array_unique_fb($uv,array('uid','loan_id'));

                halt($tem);