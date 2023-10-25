<template>
  <v-dialog
    :value="value"
    @input="$emit('input')"
    fullscreen
    hide-overlay
    transition="dialog-bottom-transition"
  >
    <v-card>
      <v-toolbar dark color="primary">
        <v-btn icon dark @click="$emit('input')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <v-toolbar-title>
          {{ integrationRun && integrationRun.id ? 'Edit' : 'New' }} Integration Run
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn dark text :loading="working" @click="saveIntegrationRun()">
            {{ integrationRun && integrationRun.id ? 'Save' : 'Create' }} Integration Run
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>

      <v-form ref="form" v-model="formValid">
        <div class="overline px-4 pt-4 pb-2">Integration Run Details</div>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.task"
              label="Integration Task"
              required
              dense
              readonly
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-select
              v-model="form.status"
              :rules="[v => !!v || 'Please select the status for this integration run']"
              :items="statusTypes"
              item-value="id"
              item-text="name"
              label="Account"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <DatePicker
              v-model="form.started"
              label="Started At"
              required
              :rules="[v => !!v || 'Please select the date time the integration run started']"
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <DatePicker
              v-model="form.completed"
              label="Completed At"
              required
              :rules="[v => !!v || 'Please select the date time the integration run started']"
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model.number="form.total"
              :rules="[v => !!v || 'Please enter the total']"
              label="Total Items"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model.number="form.success_count"
              :rules="[v => !!v || 'Please enter the number of successful synced items']"
              label="Successful"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model.number="form.failed_count"
              :rules="[v => !!v || 'Please enter the number of items that failed to sync']"
              label="Failed"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.summary_msg"
              label="Integration Summary"
              required
              dense
            />
          </v-col>
        </v-row>
        <v-row dense class="px-4">
          <v-col lg="6" sm="6" xs="12" dense>
            <v-text-field
              v-model="form.task"
              label="Integration Errors"
              required
              dense
            />
          </v-col>
        </v-row>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ManageIntegrationRun",
  props: ['value', 'integrationRun', 'integration'],
  data: () => ({
    valid: true,
    working: false,
    formValid: false,
    errors: [],
    form: {
      task: null,
      status: null,
      started: null,
      completed: null,
      total: null,
      success_count: null,
      failed_count: null,
      summary_msg: null,
      error_msg: null
    },
    statusTypes: [
      { id: 'running', name: 'Running' },
      { id: 'failed', name: 'Failed' },
      { id: 'successful', name: 'Successful' }
    ]
  }),
  async created () {
    if (this.integrationRun && this.integrationRun.id) {
      this.form.task = this.integrationRun.task
      this.form.status = this.integrationRun.status
      this.form.started = this.integrationRun.started
      this.form.completed = this.integrationRun.completed
      this.form.total = this.integrationRun.total
      this.form.success_count = this.integrationRun.success_count
      this.form.failed_count = this.integrationRun.failed_count
      this.form.summary_msg = this.integrationRun.summary_msg
      this.form.error_msg = this.integrationRun.error_msg
    }
  },
  methods: {
    async saveIntegrationRun () {
      this.working = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.working = false
        return
      }

      try {
        if (this.integrationRun && this.integrationRun.id) {
          await this.$axios.$put(`/integration_runs/${this.integrationRun.id}`, this.form)
        } else {
          await this.$axios.$post(`/integration_runs`, this.form)
        }
        this.$emit('integrationRun-saved')
        this.working = false
        this.$emit('input')
        this.reset()
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push({msg: e})
        }
        this.working = false
      }
    },
    reset () {
      this.$refs.form.resetValidation()
    }
  }
}
</script>

<style scoped>

</style>
