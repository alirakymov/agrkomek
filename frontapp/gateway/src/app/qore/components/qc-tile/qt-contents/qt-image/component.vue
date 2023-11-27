<template>
    <div class="img-container fx-img-rotate-r">
        <img class="img-responsive" :src="thumb" alt="">
        <div class="img-options">
            <div class="img-options-content">
                <h4 class="font-w400 text-white push-5">{{ title }}</h4>
            </div>
        </div>
        <div class="img-options">
            <div class="img-options-content">
                <h3 class="font-w400 text-white push-5">Image Caption</h3>
                <h4 class="h6 font-w400 text-white-op push-15">Some Extra Info</h4>
                <a class="btn btn-sm btn-default" href="javascript:void(0)"><i class="fa fa-pencil"></i> Edit</a>
                <a class="btn btn-sm btn-default" href="javascript:void(0)"><i class="fa fa-times"></i> Delete</a>
            </div>
        </div>
    </div>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
