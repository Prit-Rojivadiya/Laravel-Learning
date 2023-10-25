<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Integration</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editIntegration" v-if="!loading && pCreateEditDelete">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
            v-if="!loading && pCreateEditDelete"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn small
                     class="mr-5"
                     color="#fe8181"
                     style="float:right"
                     v-bind="attrs"
                     v-on="on">
                Delete
              </v-btn>
            </template>
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        {{ integration.name }}
      </v-card-title>
      <v-card-text class="mb-0 pb-0">
        <IntegrationOverviewComponent
          v-if="!loading"
          :integration="integration"
          v-model="integration"
        />
      </v-card-text>

      <v-container grid-list-sm justify-space-around fluid class="p-4" v-if="integration.active">
        <v-form ref="form" v-model="formValid">
          <v-layout row>
            <v-col lg="6" sm="6" xs="12" dense>
              <v-checkbox
                v-model="form.async"
                label="Run in background"
                required
                dense
              />
            </v-col>
          </v-layout>
          <v-layout row>
            <v-col lg="6" sm="6" xs="12" dense>
              <v-select
                v-model="form.task"
                :rules="[v => !!v || 'Please select the integration task to run']"
                :items="integrationTasks"
                item-value="id"
                item-text="name"
                label=" Task"
                required
                @change="taskSelected"
                dense
              />
            </v-col>
          </v-layout>
          <v-layout row v-if="showDateRange">
            <v-col lg="3" sm="4" xs="12" dense>
              <DatePicker
                v-model="form.startDate"
                label="Start Date"
                required
                :rules="[v => !!v || 'Start Date']"
              />
            </v-col>
            <v-col lg="3" sm="4" xs="12" dense>
              <DatePicker
                v-model="form.endDate"
                label="End Date"
                required
                :rules="[v => !!v || 'End Date']"
              />
            </v-col>
          </v-layout>
          <v-layout row>
            <v-col lg="6" sm="6" xs="12" dense>
              <v-btn color="green" text class="tz-greenbtn" @click="runIntegration">Run Integration</v-btn>
            </v-col>
          </v-layout>
        </v-form>
      </v-container>

      <v-alert v-if="errors.length" class="error">
        <template v-for="error in errors">
          {{ error.msg }}<br />
        </template>
        Please try again
      </v-alert>

    </v-card>

    <ManageIntegrationComponent
      v-if="showEditIntegration"
      :integration="integration"
      :client="client"
      v-model="showEditIntegration"
      @integration-saved="getDataFromApi"
    />

    <IntegrationRunsGrid
      v-if="showIntegrationRuns"
      :integration="integration"
      v-model="showIntegrationRuns"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import IntegrationOverviewComponent from '~/components/integrations/Overview'
import ManageIntegrationComponent from '~/components/integrations/ManageIntegration'
import IntegrationRunsGrid from '~/components/integration_runs/IntegrationRunsGrid'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    IntegrationRunsGrid,
    IntegrationOverviewComponent,
    ManageIntegrationComponent,
    DatePicker
  },
  data: () => ({
    loading: true,
    running: false,
    deleteDialog: false,
    showEditIntegration: false,
    showIntegrationRuns: false,
    showDateRange: false,
    integration: {
      name: null
    },
    client: null,
    pCreateEditDelete: false,
    integrationTasks: [
      { id: 'missing', name: 'Please contact TranzIT support to enable tasks for this integration' }
    ],
    formValid: false,
    errors: [],
    form: {
      task: null,
      startDate: null,
      endDate: null,
      async: true,
      dateTimeZone: null
    }
  }),
  async created () {
    if (this.$laravel.hasPermission('manage any integration')) {
      this.pCreateEditDelete = true
    }
    await this.getDataFromApi()
    if (this.integration.name === 'Geotab') {
      this.integrationTasks = [
        { id: 'TestConnection', name: 'Test Connection' },
        { id: 'MeterReadings', name: 'Meter Readings Import' },
        { id: 'FuelTax', name: 'Fuel Tax Readings Import' }
        // { id: 'DriverViolations', name: 'Driver Violations Test' },
        // { id: 'SimpleTest', name: 'Simple Test' }
      ]
    }
    const now = new Date()
    const offset = now.getTimezoneOffset()
    let tStart = new Date()
    let tEnd = new Date()
    // tStart.setDate(tStart.getDate() - 1)
    this.form.startDate = (new Date(tStart.getTime() - (offset*60*1000))).toISOString().split('T')[0] //'2022-02-23T19:21:21.469Z'
    this.form.endDate = (new Date(tEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.integration = await this.$axios.$get(`integrations/${this.$route.params.id}`)
      this.client = await this.$axios.$get(`clients/${this.integration.client_id}`)
      this.showIntegrationRuns = true
      this.loading = false
    },
    editIntegration () {
      this.showEditIntegration = true
    },
    async deleteItem () {
      await this.$axios.$delete(`integrations/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    },
    async runIntegration () {
      this.running = true
      this.errors = []
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.running = false
        return
      }
      this.form.dateTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone
      try {
        let rsp = null
        if (this.form.async) {
          rsp = this.$axios.$post(`integrations/run/${this.integration.id}`, this.form)
        }
        else {
          rsp = await this.$axios.$post(`integrations/run/${this.integration.id}`, this.form)
        }
        this.handleErrors(rsp)
        this.running = false
        this.$emit('task-completed')
        // router.push({ path: 'integration_runs/33'})
      }
      catch (e) {
        this.handleErrors(e, true)
        this.running = false
      }
      this.refreshGrid()
    },
    reset () {
      this.form = {
        task: null,
        // startDate: null,
        // endDate: null
      }
      this.$refs.form.resetValidation()
      this.showDateRange = false
    },
    taskSelected () {
      if (this.form.task !== 'TestConnection') {
        this.showDateRange = true
      }
      else {
        this.showDateRange = false
      }
    },
    handleErrors (rsp, hasError = false) {
      // check results
      if (rsp.status === 'failed') {
        this.errors.push({ msg: rsp.message })
        this.errors.push({ msg: rsp.error })
      }
      // handle exceptions
      if (hasError) {
        if (rsp.response) {
          if (rsp.response.status === 422) {
            this.errors = rsp.response.parsedErrors
          }
          else if (rsp.response.status === 500) {
            this.errors.push({ msg: rsp.response.data.message })
          }
          else {
            this.errors.push({ msg: rsp })
          }
        }
        else {
          this.errors.push({ msg: rsp })
        }
      }
    },
    refreshGrid () {
      this.showIntegrationRuns = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showIntegrationRuns = true
      });
    },
  }
}
</script>

<style scoped>
  .tz-greenbtn {
    background-color: aliceblue
  }
</style>
