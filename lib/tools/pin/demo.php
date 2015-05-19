<?php

//生成验证码图片
$pincode = new tools_pin_code('login');
$pincode->setimginfo('150','40', 'chinese');
$ret = $pincode->generate_image();

//校验验证码
$pincode = new tools_pin_code('login');
$rs = $pincode->valide_code(tools_filter::p('login_pincode', F_STR));
if($rs===true){
	echo '对了';
}
?>