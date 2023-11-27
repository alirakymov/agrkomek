<template>
    <div>
        <a v-for="button in buttons" class="btn fw-normal"
            :class="getClass(button)"
            :href="getUrl(button)"
            @click="action(button, $event)"
           ><i v-if="withIcon(button)" :class="button.icon + ' push-5-r'"></i>{{ button.label }}</a>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
