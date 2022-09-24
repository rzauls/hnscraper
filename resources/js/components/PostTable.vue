<template>
    <div class="card mt-2">
        <table id="data-table">
        </table>
    </div>
</template>

<script>

const dt = require('datatables.net');

export default {
    props: ['posts'],
    data() {
        return {
            table: null,
            postArray: [],
        }
    },
    mounted() {
        this.posts.forEach(p => {
            this.postArray.push([p.title, p.points, p.author, p.link, p.created_at, p.updated_at, "foo"])
        })
        this.table = new dt('#data-table', {
            data: this.postArray,
            columns: [
                {title: 'Title'},
                {title: 'Score'},
                {title: 'Author'},
                {title: 'Link'},
                {title: 'Created At'},
                {title: 'Last Updated'},
                {title: 'Buttons'},
            ]
        });
        console.log(this.posts)
    }
}
</script>


<style>
@import 'datatables.net-dt/css/jquery.dataTables.min.css';
</style>
