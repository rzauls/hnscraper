<template>
    <div class="card mt-2">
        <div class="card-header">{{ this.post.title }}</div>

        <div class="card-body">
            <pre>
            {{ this.post }}
        </pre>
        </div>
    </div>
</template>

<script>
export default {
    props: ['post'],
    mounted() {
        console.log('Component mounted.')
        console.log('got prop data')
        console.log(this.post)
    }
}
</script>
