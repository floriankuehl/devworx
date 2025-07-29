<?php
	use \Cascade\Runtime\Context;
	use \Cascade\Utility\CascadeUtility;
	
	$context = new Context();
	$context->set('namespaces',[
		'd' => 'Devworx\\ViewHelper',
	]);
	$context->set('defaultNamespace','Frontend\\ViewHelper');
	$context->set('PI',PI());
	$context->set('QUARTER_PI',$context->get('PI') * 0.25);
	$context->set('HALF_PI', $context->get('PI') * 0.5);
	$context->set('TWO_PI',$context->get('PI') * 2.0);
	
	$context->set('user',[
		'name' => 'Not the Master',
		'role' => 'slave'
	]);
	
	$context->set('ids',[
		'my' => 'great_id',
		'other' => 'another_id'
	]);
	
	$plots = [];
	
?>
<div class="d-flex flex-column gap-3">
	
	<h2>Assignment</h2>
	<div class="d-flex flex-row flex-wrap">
<?php
	//echo CascadeUtility::test($context, "{foo = 'bar'}","assignment",$plots);
	//echo CascadeUtility::test($context, "{user.name = 'Cool User'}","assignment",$plots);
	//echo CascadeUtility::test($context, "{bar = 12.3555}", "assignment", $plots);
	//echo CascadeUtility::test($context, "{baz = 1 + 4}", "assignment", $plots);
	//echo CascadeUtility::test($context, "{12.3555 -> sqrt() -> d:set(name:'foo')}", "assignment", $plots);
	//echo CascadeUtility::test($context, "{{barf:1234,test:'{pow(123,4)}'} -> d:set(name:'foobar')}", "assignment", $plots);
	
	/*
	echo Devworx\Utility\DebugUtility::var_dump([
		'bar' => $context->get('bar'),
		'baz' => $context->get('baz'),
		'foo' => $context->get('foo'),
		'foobar' => $context->get('foobar'),
	], 'read from context');
	*/
?>
	</div>
	
	<h2>Data Types</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//TEST: OK!
		//echo CascadeUtility::test($context, "{varString = 'Hello World'}","types",$plots);
		//echo CascadeUtility::test($context, "{varFloat = 3.1415}","types",$plots);
		//echo CascadeUtility::test($context, "{varInteger = 7}","types",$plots);
		//echo CascadeUtility::test($context, "{varConstant = true}","types",$plots);
		//echo CascadeUtility::test($context, "{varObject = {foo:'bar',bar:123.23,baz:{0:true,1:false}}}","types",$plots);
		//echo CascadeUtility::test($context, "{varArray = {0:'foo',1:123.34,2:true,3:null,4:user.name}}","types",$plots);
		
		/*
		echo Devworx\Utility\DebugUtility::var_dump([
			'varString' => $context->get('varString'),
			'varFloat' => $context->get('varFloat'),
			'varInteger' => $context->get('varInteger'),
			'varConstant' => $context->get('varConstant'),
			'varObject' => $context->get('varObject'),
			'varArray' => $context->get('varArray'),
		], 'read from context');
		*/
	?>
	</div>
	
	<h2>Functions/ViewHelper</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//TEST: OK!
		
		//echo CascadeUtility::test($context,"{upper(value:'test')}","function",$plots);
		//echo CascadeUtility::test($context, "{12.3555 -> d:format.currency(decimals:2)}", "viewhelper", $plots);
		//echo CascadeUtility::test($context,"{d:format.currency(value:1.23456,decimals:2)}","viewhelper",$plots);
		//echo CascadeUtility::test($context, "{d:if(condition:'user.age > 0',then:'Alive',else:'Dead')}", "viewhelper", $plots);
		
		echo CascadeUtility::test($context, '<d:for each="{user}" as="value" key="key">{key}: {value}</d:for>', "viewhelper", $plots);
	?>
	</div>
	
	<h2>Math</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//TEST: OK!
		//echo CascadeUtility::test($context, "{1 + (2 * 3) / 4}", "math", $plots);
		//echo CascadeUtility::test($context, "{floor( QUARTER_PI * cos(1.5 / 0.23) ) + ceil( HALF_PI * sin(3.54 / 0.333) )}", "math", $plots);	
		//echo CascadeUtility::test($context, "{mega = 1 + ( user.name == 'Master' ? 2 : 3 ) * sqrt(4) -> d:format.number(decimals:4)}", "math", $plots);
		/*
		echo CascadeUtility::test( $context, '{
			abs(
				(x > 0 ? sin(x) : cos(-x)) 
				* pow(max(QUARTER_PI, y ?? 1), 2)
				/ (1 + sqrt(z ?? 0))
			)
			+ (isActive && !isHidden ? 42 : floor(random() * 100)) 
			- ceil( HALF_PI * tan(angle / 360 * PI) )
		}', 'math', $plots );
		*/
	?>
	</div>
		
	<h2>Unary operations</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "{++1}", "unary", $plots);
		//echo CascadeUtility::test($context, "{--1}", "unary", $plots);
		//echo CascadeUtility::test($context, "{!true}", "unary", $plots);
		//echo CascadeUtility::test($context, "{+5}", "unary", $plots);
		//echo CascadeUtility::test($context, "{-5}", "unary", $plots);
	?>
	</div>
	
	<h2>Binary operations</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "{1 + 2}", "binary", $plots);
		//echo CascadeUtility::test($context, "{2 - 3}", "binary", $plots);
		//echo CascadeUtility::test($context, "{4 * 5}", "binary", $plots);
		//echo CascadeUtility::test($context, "{2 ** 8}", "binary", $plots);
		//echo CascadeUtility::test($context, "{2 / 8}", "binary", $plots);
		//echo CascadeUtility::test($context, "{4 % 2}", "binary", $plots);
		
		//echo CascadeUtility::test($context, "{user.age ?? 'n/a'}", "binary", $plots);
		
		//echo CascadeUtility::test($context, "{3 <> 4}", "math", $plots);
		//echo CascadeUtility::test($context, "{3 <= 4}", "math", $plots);
		//echo CascadeUtility::test($context, "{3 > 4 && 4 <= 10 && 13 <> 25}", "math", $plots);
	?>
	</div>
	
	<h2>Ternary operations</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "{user.role == 'master' ? 'Master' : 'Slave'}", "ternary", $plots);
	?>
	</div>
	
	<h2>Bitweise operations</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "{64 & 7}", "bitwise", $plots);
		//echo CascadeUtility::test($context, "{64 | 7}", "bitwise", $plots);
		//echo CascadeUtility::test($context, "{64 ^ 7}", "bitwise", $plots);
		//echo CascadeUtility::test($context, "{64 !^ 7}", "bitwise", $plots);
		//echo CascadeUtility::test($context, "{64 << 7}", "bitwise", $plots);
		//echo CascadeUtility::test($context, "{64 >> 4}", "bitwise", $plots);
	?>
	</div>
	
	<h2>Piping</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "{1.20619 -> sqrt() -> pow(b:85) -> ceil() -> d:format.number(decimalSeparator:' labara ',thousandsSeparator:' miez ',decimals:2)}", "pipe", $plots);
		//echo CascadeUtility::test($context, "{user.name -> d:format.raw()}", "pipe", $plots);
		//echo CascadeUtility::test($context, "{split(a:user.name) -> d:format.json()}", "pipe", $plots);
		//echo CascadeUtility::test($context, "{{foo:'bar'} -> {bar:'foo'} -> d:format.json()}", "pipe", $plots);	
	?>
	</div>
	
	<h2>HTML</h2>
	<div class="d-flex flex-row flex-wrap">
	<?php
		//echo CascadeUtility::test($context, "<div id=\"{ids.my}\">Hello {user.name}</div>", "html", $plots);
		//echo CascadeUtility::test($context, "<div class=\"d-flex flex-row text-bg-{user.role}\">Spot on {user.role}</div>", "html", $plots);
	?>
	</div>
</div>
<?php
	\Devworx\Utility\FileUtility::setJson(
		"test/cascade.json",
		$plots
	);	
?>