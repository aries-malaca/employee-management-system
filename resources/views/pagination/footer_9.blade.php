<button v-bind:disabled="pagination[9].current_page==1" class="btn btn-sm"
@click="setPage(pagination[9].current_page - 1,9)">
Previous
</button>
<button v-bind:disabled="pagination[9].current_page==Math.ceil(pagination[9].original_length/pagination[9].per_page) || pagination[9].original_length==0"
@click="setPage(pagination[9].current_page + 1,9)" class="btn btn-sm">
Next
</button><br/><br/>
<span v-text=" 'Showing ' + pagination[9].current_page + ' Page of ' + Math.ceil(pagination[9].original_length/pagination[9].per_page) + ' of ' + pagination[9].original_length + ' entries'"></span>
