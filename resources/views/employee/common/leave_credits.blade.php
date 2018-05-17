<h3>Leave Credits for {{ date('Y') }} &nbsp; <span v-if="newEmployee !== undefined"><button @click="editLeaveCredit" v-if="newEmployee.delete_attendance == true" type="button" class="btn green btn-sm">Adjust Leave Credit</button></span> </h3>
<table class="table-hover table table-bordered">
    <thead>
        <tr>
            <th>Leave Type</th>
            <th>Used (Includes: Pending)</th>
            <th>Credits</th>
            <th>Max</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="leave in leave_types" v-if="leave.leave_type_active===1">
            <td>@{{ leave.leave_type_name }} (@{{ leave.leave_type_data.paid=='true'?'PAID':'UNPAID' }})</td>
            <td>@{{ leave.used }}</td>
            <td>@{{ leave.credits }}</td>
            <td>@{{ Number(leave.used) + Number(leave.credits) }}</td>
        </tr>
    </tbody>
</table>