<template>
    <div class="row" :class="styleClass">
        <component v-for="component in components"
            :key="component.id"
            :is="component.type"
            :options="component.data"
        ></component>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
