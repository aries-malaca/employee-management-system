<!DOCTYPE html>

<html lang="en" class="no-js">

<head>
    <!-- If you delete this meta tag, Half Life 3 will never be released. -->
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>{{$config['app_name']}} | Attendance Notice</title>
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

<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td class="container" bgcolor="#FFFFF2">
            <div class="content">
                <p>Hi {{ $branch['branch_name'] }},</p>
                <p><b>{{ $user['name']  }}</b> missed having an attendance on date: {{ json_decode($notification['notification_data'])->date }}</p>
                <p>If {{ $user['gender']=='male'?'he was':'she was' }} present on that day please advice {{ $user['gender']=='male'?'him':'her' }} to file a corresponding employee form.</p>
                <p>If Rest day, kindly ask your A.S. To Adjust your schedule.</p>
                <br/>

                <p>Visit EMS: <a href="http://ems.lay-bare.com/login">http://ems.lay-bare.com/login</a> to monitor branch Employee's Time card</p>

                <br/>
                <p>Thank you!</p>
            </div><!-- /content -->
        </td>
    </tr>
</table><!-- /BODY -->

<div class="footer">
    {{ date('Y') }} &copy; {{$config['app_name']}}
</div>

</body>
</html>