<!DOCTYPE html>

<html lang="en" class="no-js">

<head>
    <!-- If you delete this meta tag, Half Life 3 will never be released. -->
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>{{$config['app_name']}} | Forgot Password</title>
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
                <p>Hi {{ $user['name'] }},</p>
                <p>You have new Employee Request for approval: </p>
                @include('emails.request_data')
                <br/>
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