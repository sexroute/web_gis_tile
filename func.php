<?php
/**
 * Created by PhpStorm.
 * User: dragon-w
 * Date: 16/5/2
 * Time: 12:01
 */

function dump($info){

    echo '<pre>';
    print_r($info);
    echo '</pre>';

}

//得到某一层级的行数
function get_rows($level){

    if($level==0){

        return 0;

    }else{

        return get_rows($level-1)*2+1;
    }

}


//切图函数
function create_tile($source_path,$z){


    if(!file_exists($source_path)){

        exit($source_path.'文件不存在');

    }

    $source_info   = getimagesize($source_path);

    $source_width  = $source_info[0];
    $source_height = $source_info[1];


    $path = __DIR__.'/nativegis2/' . $z.'/';


    if (!file_exists($path)) { //层级

        @mkdir($path);
    }


    $level=ceil($source_height/256);


    if($level>10){

        exit('原文件分辨率过大,建议使用2560*1600以下的图片');
    }

    $rows=get_rows($z+1); //层级比行标号大1

    for ($x = 0; $x <$rows; $x++) {  //行


        $path = __DIR__ . '/nativegis2/' . $z . '/' . $x . '/';

        if (!file_exists($path)) {

            @mkdir($path);
        }


        for ($y = 0; $y <=(pow(2,$z)-1); $y++) {  //瓦片图

            $img_path= __DIR__.'/nativegis2/' . $z.'/'.$x.'/'.$y.'.png';

            $source_x=$x*256; //列宽
            $source_y=$y*256; //行高


            if($source_x>$source_width || $source_y > $source_height){ //在行或者列上已经没有可切的了

                copy('none.png',$img_path);

            }else{

                $target_x=$target_y=256;

                if(($x+1)*256 > $source_width ){ //宽度上不够了

                    $target_x=$source_width%256;

                }

                if(($y+1)*256 > $source_height){ //高度上不够了

                    $target_y=$source_height%256;

                }


                $source_image = imagecreatefrompng($source_path);
                $target_image  = imagecreatetruecolor(256,256); //创建图片对象

                imagesavealpha($target_image, true);
                $trans_colour = imagecolorallocatealpha($target_image, 0, 0, 0, 127);
                imagefill($target_image, 0, 0, $trans_colour);

                imagecopy($target_image, $source_image, 0, 0, $source_x, $source_y, $target_x, $target_y);
                imagepng($target_image,$img_path);

                imagedestroy($source_image);
                imagedestroy($target_image);
            }


        }

    }
}