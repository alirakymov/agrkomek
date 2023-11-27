<template>
    <a class="block block-bordered block-link-hover3" href="javascript:void(0)">
        <table class="block-table text-center">
            <tbody>
                <tr>
                    <td class="bg-gray-lighter border-r" style="width: 50%;">
                        <div class="push-30 push-30-t">
                            <i :class="icon + ' fa-3x'"></i>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="h1 font-w700">{{ data }}</div>
                        <div class="h5 text-muted text-uppercase push-5-t">{{ title }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </a>
</template>
<script src="./script.js"></script>
<style src="./style.css"></style>
