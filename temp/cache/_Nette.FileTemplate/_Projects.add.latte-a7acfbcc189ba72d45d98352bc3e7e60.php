<?php //netteCache[01]000343a:2:{s:4:"time";s:21:"0.10990900 1334527661";s:9:"callbacks";a:2:{i:0;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:9:"checkFile";}i:1;s:54:"C:\xampp\htdocs\Timak\app\templates\Projects\add.latte";i:2;i:1334482612;}i:1;a:3:{i:0;a:2:{i:0;s:6:"NCache";i:1;s:10:"checkConst";}i:1;s:20:"NFramework::REVISION";i:2;s:30:"013c8ee released on 2012-02-03";}}}?><?php

// source file: C:\xampp\htdocs\Timak\app\templates\Projects\add.latte

?><?php
// prolog NCoreMacros
list($_l, $_g) = NCoreMacros::initRuntime($template, 'cin7vgpnrp')
;
// prolog NUIMacros
//
// block breadcrumb
//
if (!function_exists($_l->blocks['breadcrumb'][] = '_lb2e3b21e377_breadcrumb')) { function _lb2e3b21e377_breadcrumb($_l, $_args) { extract($_args)
?><li><a href="<?php echo htmlSpecialChars($_control->link("Homepage:default")) ?>">Domov</a></li>
<li>></li>
<li><a href="<?php echo htmlSpecialChars($_control->link("Projects:default")) ?>">Projekty</a></li>
<li>></li>
<li class="actual">Pridať projekt</li>
<?php
}}

//
// block content
//
if (!function_exists($_l->blocks['content'][] = '_lbcd347d62da_content')) { function _lbcd347d62da_content($_l, $_args) { extract($_args)
?><h2>Pridanie nového projektu</h2>

<div class="forms">
	<div class="add-project">
<?php $_ctrl = $_control->getComponent("addForm"); if ($_ctrl instanceof IRenderable) $_ctrl->validateControl(); $_ctrl->render() ?>
	</div>
</div>
<?php
}}

//
// end of blocks
//

// template extending and snippets support

$_l->extends = empty($template->_extended) && isset($_control) && $_control instanceof NPresenter ? $_control->findLayoutTemplateFile() : NULL; $template->_extended = $_extended = TRUE;


if ($_l->extends) {
	ob_start();

} elseif (!empty($_control->snippetMode)) {
	return NUIMacros::renderSnippets($_control, $_l, get_defined_vars());
}

//
// main template
//
if ($_l->extends) { ob_end_clean(); return NCoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }
call_user_func(reset($_l->blocks['breadcrumb']), $_l, get_defined_vars())  ?>

<?php call_user_func(reset($_l->blocks['content']), $_l, get_defined_vars()) ; 