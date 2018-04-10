<template>
    <span>
        <a class="submission-nav-link" href="javascript:void(0)" v-on:click="showModal">Report</a>

        <template v-if="modalShown">
            <div class="modal-container" v-on:click="closeModal">
                <div class="modal-pane" v-on:click.stop>
                    <div class="modal-header">
                          <translated-text :message="currentHeader" />
                    </div>

                    <div class="modal-content">
                        <!-- Initial report selection -->
                        <template v-if="reportStep === 'initialSelection'">
                            <form>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="rules" />
                                        It breaks f/{{forum}}'s rules
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="This is spam" />
                                        This is spam
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="This is abusive or harassing" />
                                        This is abusive or harassing
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="other" />
                                        Other issues
                                    </label>
                                </div>
                            </form>
                        </template>

                        <!-- Rule break selection -->
                        <template v-if="reportStep === 'ruleSelect'">
                            <form>
                                <div>
                                    <textarea class="report-text" style="width: 100%" v-model="reportBody" />
                                </div>
                            </form>
                        </template>

                        <!-- Other issue report selection -->
                        <template v-if="reportStep === 'otherSelect'">
                            <form>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It infringes my copyright" />
                                        It infringes my copyright
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It infringes my trakemark rights" />
                                        It infringes my trakemark rights
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It's personal and confidential information" />
                                        It's personal and confidential information
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It's sexual or suggestive content involving minors" />
                                        It's sexual or suggestive content involving minors
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It's involuntary pornography" />
                                        It's involuntary pornography
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" :name="reportRadioName" v-model="reportBody" value="It's a transaction for prohibited goods or services" />
                                        It's a transaction for prohibited goods or services
                                    </label>
                                </div>
                            </form>
                        </template>

                        <!-- loading spinner -->
                        <template v-if="reportStep === 'loading'">
                            <div class="text-center fa-2x" style="padding: 50px;">
                                <i class="fas fa-circle-notch fa-spin"></i>
                            </div>
                        </template>

                        <!-- Finished view -->
                        <template v-if="reportStep === 'finished'">
                            <div v-if="!hasBlocked">
                              <p>We've notified the moderators of f/{{forum}}. In the meantime, here are some other actions you can take.</p>

                              <div style="line-height: 130%; cursor: pointer;" v-on:click="blockUser">
                                <span class="fa-layers fa-fw fa-3x text-accent" style="float: left">
                                  <i class="fas fa-ban" data-fa-transform="row-2"></i>
                                  <i class="fas fa-user" data-fa-transform="shrink-7"></i>
                                </span>

                                <span class="text-larger">Block {{userName}}</span>
                                <br />
                                <span class="text-accent text-smaller">You won't see posts or comments from {{userName}}. You can change this later in your preferences.</span>
                              </div>
                            </div>
                            <div v-if="hasBlocked" :class="blockResultStatus == 'error' ? 'text-error' : 'text-success'">
                                {{blockResultMessage}}
                            </div>
                        </template>
                    </div>

                    <div class="modal-foot flex-no-wrap flex-align-end">
                        <div class="flex-grow-5 text-smaller text-accent">
                            <translated-text message="report_modal.additional_info" v-bind:ishtml="true" v-bind:params="[forum]" />
                        </div>
                        <div class="flex-grow-1 text-right" v-if="!isLoading">
                            <button type="button" class="button button--secondary" v-if="cancelVisible" v-on:click="closeModal">Cancel</button>
                            <button type="button" class="button button--secondary" v-if="backVisible" v-on:click="backStep">Back</button>
                            <button type="button" class="button button--secondary" v-if="closeVisible" v-on:click="closeModal">Close</button>
                            <button type="button" class="button" v-if="nextVisible" v-on:click="nextStep">Next</button>
                            <button type="button" class="button" :disabled="submitDisabled" v-if="submitVisible" v-on:click="doSubmit">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </span>
</template>

<script>
import TranslatedText from './translatedtext';

export default {
    name: "report-link",
    mounted() {
    },
    data() {
        return {
            modalShown: false,
            currentHeader: "report_modal.initial_header",
            reportStep: "initialSelection",
            reportRadioName: "report-radio-" + Math.floor(Math.random() * 1000000),
            reportBody: "",
            isLoading: false,
            hasBlocked: false,
            blockResultStatus: "",
            blockResultMessage: ""
        };
    },
    props: {
        forum: { type: String },
        submissionId: { type: String },
        csrfToken: { type: String },
        userName: { type: String }
    },
    methods: {
        showModal: function() {
            this.$data.modalShown = true;
        },
        closeModal: function() {
            // Reset the boject state.
            Object.assign(this.$data, this.$options.data.call(this));
        },
        nextStep: function() {
            if(this.$data.reportBody == "rules") {
                this.$data.reportStep = "ruleSelect";
                this.$data.currentHeader = "report_modal.rule_select_header";
                this.$data.reportBody = "";
            }

            if(this.$data.reportBody == "other") {
                this.$data.reportStep = "otherSelect";
                this.$data.currentHeader = "report_modal.other_select_header";
            }
        },
        backStep: function() {
            this.$data.reportStep = "initialSelection";
            this.$data.currentHeader = "report_modal.initial_header";
        },
        doSubmit: function() {
            var self = this;
            this.$data.reportStep = "loading";
            this.$data.isLoading = true;

            var reportLink = "/f/" + this.forum + "/" + this.submissionId + "/report";
            $.post(reportLink, { reportBody: this.$data.reportBody, token: this.csrfToken }, function() {
                self.$data.reportStep = "finished";
                self.$data.isLoading = false;
                self.$data.currentHeader = "report_modal.finished_header";
            });
        },
        blockUser: function() {
            var self = this;
            this.$data.reportStep = "loading";
            this.$data.isLoading = true;

            var reportLink = "/block_json/" + this.userName;
            $.post(reportLink, function(data) {
                self.$data.reportStep = "finished";
                self.$data.isLoading = false;
                self.$data.hasBlocked = true;
                self.$data.blockResultStatus = data.status;
                self.$data.blockResultMessage = data.message;
            });
        }
    },
    computed: {
        submitVisible: function() {
            if(this.closeVisible) return false;

            var result = true;
            if(this.$data.reportStep == "initialSelection") {
                var result = (this.$data.reportBody != "rules" && this.$data.reportBody != "other") ? true : false;
            }
            return result;
        },
        nextVisible: function() {
            if(this.closeVisible) return false;
            if(this.$data.reportBody == "") return false;

            return !this.submitVisible;
        },
        cancelVisible: function() {
            if(this.closeVisible) return false;
            return (this.$data.reportStep == "initialSelection");
        },
        backVisible: function() {
            if(this.closeVisible) return false;
            return !this.cancelVisible;
        },
        closeVisible: function() {
            return (this.$data.reportStep == "finished");
        },
        submitDisabled: function() {
            return (this.$data.reportBody.trim() == "");
        }
    },
    components: {TranslatedText}
}
</script>

<style>
</style>
