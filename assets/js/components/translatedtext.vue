<template>
    <span>
        <template v-if="!ishtml">{{text}}</template>
        <template v-if="ishtml"><span v-html="text">...</span></template>
    </span>
</template>

<script>
// Rudimentary string format function (thx stackoverflow).
if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

export default {
    name: "translated-text",
    mounted: function() { },
    props: {
        message: { type: String },
        ishtml: { type: Boolean, default: false },
        params: { type: Array, default: function () { return []; } }
    },
    computed: {
        text: function() {
            var result = Translator.trans(this.message);

            if(this.params.length > 0)
                result = result.format(this.params);

            return result;
        }
    }
}
</script>

<style scoped>

</style>
