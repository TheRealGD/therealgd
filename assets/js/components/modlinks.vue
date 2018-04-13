<template>
  <div v-bind:ref="'root-ele'">
    <div class="mod-links">
      <span class='mod-label mod-label-reportcount' v-on:click="fetchReports">Reports: {{reportCount}}</span>
      <!-- <span class='mod-label mod-label-spam'>Spam</span> -->
      <span class='mod-label mod-label-approve' v-on:click="doApprove">Approve <i v-if="isRunningApprove" class="fas fa-circle-notch fa-spin"></i></span>
      <span class='mod-label mod-label-remove' v-on:click="doRemove">Remove <i v-if="isRunningRemove" class="fas fa-circle-notch fa-spin"></i></span>
      <!-- <span class='mod-label mod-label-ignore'>Ignore reports</span> -->
    </div>

    <div class='mod-reports' v-if="reportsShown">
      <div v-if="isLoading">
        <div class="text-center fa-2x" style="padding: 50px;">
          <i class="fas fa-circle-notch fa-spin"></i>
        </div>
      </div>

      <div v-if="!isLoading">
        User reports:
        <ol>
          <li v-for="entry in reportEntries">{{ entry.body }}</li>
        </ol>
      </div>
    </div>
  </div>
</template>

<script>
import TranslatedText from './translatedtext';

export default {
    name: "mod-links",
    mounted() {
    },
    data() {
        return {
            reportsShown: false,
            isLoading: false,
            isRunningRemove: false,
            isRunningApprove: false,
            reportEntries: []
        };
    },
    props: {
        forum: { type: String },
        reportCount: { type: String },
        submissionId: { type: String },
        commentId: { type: String, default: null }
    },
    methods: {
        fetchReports: function() {
            if(this.$data.reportShown) {
                this.$data.reportsShown = false;
                return;
            }

            var reportUrl;
            var self = this;

            this.$data.isLoading = true;
            this.$data.reportsShown = true;

            if(this.commentId == null)
                reportUrl = "/f/" + this.forum + "/" + this.submissionId + "/report_entries";
            else
                reportUrl = "/f/" + this.forum + "/" + this.submissionId + "/comment/" + this.commentId + "/report_entries";

            $.get(reportUrl, function(data) {
                self.$data.isLoading = false;
                self.$data.reportEntries = data;
            });
        },
        doRemove: function() {
            var self = this;

            if(this.$data.isRunningRemove) return;
            this.$data.isRunningRemove = true;

            // Remove the comment/submission from the DOM.
            if(this.commentId == null) {
                $.post("/f/" + this.forum + "/" + this.submissionId + "/report_action", { reportAction: 'remove' }, function(data) {
                    $(self.$refs['root-ele']).parent().parent().parent().slideUp(100);
                });
            } else {
                $.post("/f/" + this.forum + "/" + this.submissionId + "/comment/" + this.commentId + "/report_action", { reportAction: 'remove' }, function(data) {
                    $(self.$refs['root-ele']).parent().parent().parent().slideUp(100);
                });
            }
        },
        doApprove: function() {
            var self = this;

            if(this.$data.isRunningApprove) return;
            this.$data.isRunningApprove = true;

            // Remove the comment/submission links from the DOM.
            if(this.commentId == null) {
                $.post("/f/" + this.forum + "/" + this.submissionId + "/report_action", { reportAction: 'approve' }, function(data) {
                    $(self.$refs['root-ele']).fadeOut(100);
                });
            } else {
                $.post("/f/" + this.forum + "/" + this.submissionId + "/comment/" + this.commentId + "/report_action", { reportAction: 'approve' }, function(data) {
                    $(self.$refs['root-ele']).fadeOut(100);
                });
            }
        }

    },
    computed: {
    },
    components: {TranslatedText}
}
</script>
