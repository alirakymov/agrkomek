<template>
    <form v-on:submit.prevent="onSubmit" :action="action" :method="method" class="row">
        <component v-for="field in fields"
            :key="field.id"
            :is="field.ctype"
            :schema="field.data"
            :id="field.id"
            :name="field.name"
            :model="model"
            :errors="errors"
            :form="formInstance"
        ></component>
    </form>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
