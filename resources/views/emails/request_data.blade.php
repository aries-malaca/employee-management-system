<p> Employee: {{ $notification['name'] }}  <br/>
    Request Type: {{ $notification['request_type'] }} <br/>
    @if($notification['request_type'] == 'Adjustment')
        Date: {{ date('m/d/Y',strtotime($notification['data'][0]['date'])) }} <br/>
        Time: {{ date('h:i A',strtotime($notification['data'][0]['time'])) }} <br/>
        Mode: {{ $notification['data'][0]['mode'] }} <br/>
    @elseif($notification['request_type'] == 'Travel' OR $notification['request_type'] == 'Offset' OR $notification['request_type'] == 'Overtime')
        Start: {{ date('m/d/Y h:i A',strtotime($notification['data'][0]['date_start'] .' '. $notification['data'][0]['time_start'] )) }} <br/>
        End: {{ date('m/d/Y h:i A',strtotime($notification['data'][0]['date_end'] .' '. $notification['data'][0]['time_end'] )) }} <br/>
    @elseif($notification['request_type'] == 'Schedule')
        Date: {{ date('m/d/Y',strtotime($notification['data'][0]['date'])) }}<br/>
        @if($notification['data'][0]['time'] != '00:00')
            Time: {{ date('H:i A',strtotime($notification['data'][0]['date'] .' '. $notification['data'][0]['time'])) }}<br/>
        @else
            Time: Rest Day<br/>
        @endif
        Branch: {{$notification['data'][0]['branch_name'] }} <br/>
    @else
        Date: {{ date('m/d/Y',strtotime($notification['data'][0]['date_start'])) }}
        @if($notification['data'][0]['date_start'] != $notification['data'][0]['date_end'])
            - {{ date('m/d/Y',strtotime($notification['data'][0]['date_end'])) }}
        @endif
        <br/>
        Mode: {{ $notification['data'][0]['mode'] }} <br/>
    @endif

    @if($notification['request_type'] == 'Leave')
        Days: {{ $notification['data'][0]['days']  }} <br/>
        Leave Type: {{ $notification['data'][0]['leave_type_name']  }} <br/>
    @endif
    Notes: {{ $notification['notes'] }} <br/>
</p>