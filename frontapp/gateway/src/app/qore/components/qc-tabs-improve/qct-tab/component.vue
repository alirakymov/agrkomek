<template>
    <div class="w-100">
        <component v-for="component in components"
            :key="component.id"
            :is="component.type"
            :options="component.data"
        ></component>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
