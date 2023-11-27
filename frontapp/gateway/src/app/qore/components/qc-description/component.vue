<template>
    <dl>
        <template v-for="(column, key) in columns">
            <dt :class="getHeaderClass(column)">{{ column.label }}</dt>
            <dd :class="getColumnClass(column)">{{ model[key] }}</dd>
        </template>
    </dl>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
