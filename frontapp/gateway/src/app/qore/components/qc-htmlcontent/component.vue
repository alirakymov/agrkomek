<template>
    <div>
        <div class="row">
            <div v-if="!inBlock && title" class="col-sm-3 push-10-t">
                <h5 class="">{{ title }}</h5>
            </div>
            <div v-if="!inBlock" class="col-sm-3">
                <div class="pull-right push-10">
                    <a class="btn btn-default btn-sm" v-for="action in actions" v-on:click="componentActionClick(action, $event)"><i :class="componentActionIcon(action)"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" v-html="content"></div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
