<template>
    <div class="card p-2">
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
            this.postArray.push(
                {
                    title: p.title,
                    points: p.points,
                    author: p.author,
                    link: p.link,
                    created_at: p.created_at,
                    updated_at: p.updated_at,
                    id: p.id,
                },
            )
        })
        this.table = new dt('#data-table', {
            data: this.postArray,
            columns: [
                {
                    title: 'Title',
                    data: 'title',
                    render: function (data, type, row) {
                        // render a clickable title for the post
                        if (type === 'display') {
                            return `<a href="` + row.link + `">` + data + `</a>`
                        }
                        return data
                    }
                },
                {title: 'Score', data: 'points'},
                {title: 'Author', data: 'author'},
                {title: 'Created At', data: 'created_at', render: dt.render.datetime()},
                {title: 'Last Updated', data: 'updated_at', render: dt.render.datetime()},
                {
                    title: 'Actions',
                    data: 'id',
                    render: function (data, type, row) {
                        // render a clickable delete button
                        if (type === 'display') {
                            // TODO: post a delete with confirmation on click
                            return `<a href="/delete/` + data + `">Delete</a>`
                        }
                        return data
                    }
                },
            ]
        });
    }
}
</script>


<style>
@import 'datatables.net-dt/css/jquery.dataTables.min.css';
</style>
