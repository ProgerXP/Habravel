<!DOCTYPE html>
<html lang="{{{ Config::get('app.locale') }}}">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>{{{ trans('habravel::g.remindPassword.mailSubject') }}}</h2>
		<div>
			<p>{{ trans('habravel::g.remindPassword.mailText', array('url' => $url)) }}</p>
		</div>
	</body>
</html>
