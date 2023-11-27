<template>
    <ul class="nav nav-pills push">
        <li v-for="item in items" class="nav-item">
            <a class="nav-link fw-normal" :class="{'active' : item.active}" :href="item.url">{{ item.label }}</a>
        </li>
    </ul>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
