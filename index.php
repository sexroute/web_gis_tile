<?php
/**
 * Created by PhpStorm.
 * User: dragon-w
 * Date: 16/5/1
 * Time: 11:06
 */
include 'worker.php';
set_time_limit(0);

define('DEFAULT_LEVEL',4);

/*
 * 1.读取源图,计算出原图的缩放等级:$level=>ceil(高度/256),
 * 2.循环缩放原图$level次,得到每层的原图
 * 3.分别读取每层的原图,获取其的宽度和高度, 得到 网格的行与列的数量,$row = ceil(width/256),$col=ceil(height/256)
 * 4.使用两个for循环,裁剪该层图片,并存储到对应的文件夹中
 */


//1
$source_path="./ditu.png";

if(!file_exists($source_path)){

    exit('源文件不存在!');
}

$source_info   = getimagesize($source_path);

$source_width  = $source_info[0];
$source_height = $source_info[1];
$source_ratio  = $source_height/$source_width; //

$level=ceil($source_height/256);


//临时图层目录处理
$root=__DIR__.'/temp/';

if(!file_exists($root)){

    mkdir($root);

}else{

    if($handle=opendir($root)){

        while(false!==($item=readdir($handle))){

            if($item=="." || $item==".."){

                continue;
            }

            $item=rtrim($path,"/")."/".$item;

            if(is_dir($item)){
                continue;
            }
            @unlink($item);
        }
    }
}



//对原图进行缩放处理
for($i=0;$i<$level;$i++){



    $target_path=$root.$i.'.png';

    $target__test_path=$root.($i-1).'.png';

    //如果已经到达最大分辨率,则停止处理
    if(file_exists($target__test_path)){

        $source__test_info   = getimagesize($target__test_path);
        $tmp_source_height  = $source__test_info[1];

        if($tmp_source_height>=256*DEFAULT_LEVEL){

            $level=ceil($tmp_source_height/256); //调整level级别
            break;
        }

    }

    $source_image = imagecreatefrompng($source_path);


    if($i==0){

        $target_width=256;
        $target_height=256*$source_ratio;

    }else{

        $target_width=256+$i*256;
        $target_height=(256+$i*256)*$source_ratio;
    }


    $target_image  = imagecreatetruecolor($target_width, $target_height); //创建图片对象
    imagesavealpha($target_image, true);
    $trans_colour = imagecolorallocatealpha($target_image, 0, 0, 0, 127);
    imagefill($target_image, 0, 0, $trans_colour);

    imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width,$source_height);

    if (!file_exists($root)) {

        @mkdir($root);
    }

    imagepng($target_image,$target_path);

    imagedestroy($source_image);
    imagedestroy($target_image);

}



$tmp=array();

$work_tile_map=array();

//生成层级切图任务列表
for($z=0;$z<$level;$z++){

    $source_path=$root.$z.'.png';

    $work_tile_map[]=array(
        'z'=>$z,
        'sourse_path'=>$source_path
    );
}


//循环切图
foreach($work_tile_map as $val){

    create_tile($val['sourse_path'],$val['z']);

    echo "done...";

}