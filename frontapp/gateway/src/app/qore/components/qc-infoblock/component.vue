<template>
    <div class="alert m-0" role="alert" :class="alertType">
        <h3 v-if="title" class="alert-heading h4 my-2">{{ title }}</h3>
        <p v-html="content"></p>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
