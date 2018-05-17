<!DOCTYPE html>

<html lang="en" class="no-js">

<head>
    <!-- If you delete this meta tag, Half Life 3 will never be released. -->
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>{{$config['app_name']}} | Birthday Greetings</title>
    @include('emails.style')
</head>

<body bgcolor="#FFFFFF">
<!-- HEADER -->
<table class="head-wrap" bgcolor="#2b3643">
    <tr>
        <td class="header container" >
            <img style="margin:3px 10px; width:140px; height:38px;" src="{{url('images/app/hr-logo.png')}}" alt="logo">
        </td>
    </tr>
</table><!-- /HEADER -->
<br/>
<!-- BODY -->
@if($type == 'daily')
    <h4 style="text-align:center">Today's Birthday {{ (sizeof($celebrants)>1?'Celebrants':'Celebrant') }}</h4>
@else
    <h4 style="text-align:center">This Month's Birthday {{ (sizeof($celebrants)>1?'Celebrants':'Celebrant') }}</h4>
@endif
<table class="body-wrap">
    <tr>
        <th>Name</th>
        <th>Department</th>
        <th>Position</th>
        <th>Birth Date</th>
    </tr>
    @foreach($celebrants as $celebrant)
    <tr>
        <td style="text-align:center">{{$celebrant['name']}}</td>
        <td style="text-align:center">{{$celebrant['department_name']}}</td>
        <td style="text-align:center">{{$celebrant['position_name']}}</td>
        <td style="text-align:center">{{ date('m/d/Y',strtotime($celebrant['birth_date'])) }}</td>
    </tr>
    @endforeach
</table><!-- /BODY -->
<br/>
<div class="footer">
    {{ date('Y') }} &copy; {{$config['app_name']}}
</div>

</body>
</html>