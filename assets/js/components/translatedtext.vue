<template>
    <span :data-translation-key="text">
        <template v-if="!ishtml">{{messageText}}</template>
        <template v-if="ishtml"><span v-html="messageText">...</span></template>
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
    data: function() {
      return {
        messageText: ""
      }
    },
    computed: {
        text: function() {
            var self = this;
            setTimeout(() => { self.$data.messageText = Translator.trans(self.message).format(self.params); }, 0);
            return this.message;
        }
    }
}
</script>

<style scoped>

</style>
