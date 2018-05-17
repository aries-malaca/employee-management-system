<button v-bind:disabled="pagination[2].current_page==1" class="btn btn-sm"
@click="setPage(pagination[2].current_page - 1,2)">
Previous
</button>
<button v-bind:disabled="pagination[2].current_page==Math.ceil(pagination[2].original_length/pagination[2].per_page) || pagination[2].original_length==0"
@click="setPage(pagination[2].current_page + 1,2)" class="btn btn-sm">
Next
</button><br/><br/>
<span v-text=" 'Showing ' + pagination[2].current_page + ' Page of ' + Math.ceil(pagination[2].original_length/pagination[2].per_page) + ' of ' + pagination[2].original_length + ' entries'"></span>
