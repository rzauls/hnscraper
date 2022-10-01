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
            language: {
                emptyTable: "No entries have been fetched, try running `artisan fetch:posts` command in your console"
            },
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
                        // im pretty sure there is a better way to delete a specific item, however let's keep it simple
                        if (type === 'display') {
                            return `
                                <form id="delete-` + data + `" action="/posts/` + data + `" method="POST" style="display: none;">
                                    <input type="hidden" name="_token" value="` + document.querySelector('meta[name="csrf-token"]').getAttribute('content') + `">
                                    <input type="hidden" name="_method" value="delete">
                                </form>
                                <button class="delete-item-button" form="delete-` + data + `">Delete</button>
                            `
                        }
                        return data
                    },
                },
            ]
        });
    }
}
</script>


<style>
@import 'datatables.net-dt/css/jquery.dataTables.min.css';
</style>
