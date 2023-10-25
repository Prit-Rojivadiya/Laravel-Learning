<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>{{integration.name}} {{integrationRun.task | formatSplitWordsByCapitalize}} Run: {{ integrationRun.started | formatDateMDYhhmmss }}</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <span v-if="loading" class="ml-5 k-i-loading"/>
          <v-btn small color="info" style="float: left" @click="refreshAll">Refresh</v-btn>
          <v-btn small color="info" style="float: right" class="mr-5" @click="editIntegrationRun">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                small
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
        {{ integrationRun.task | formatSplitWordsByCapitalize }}
      </v-card-title>
      <v-card-text>
        <IntegrationRunOverviewComponent
          v-if="!loading && showOverview"
          :integrationRun="integrationRun"
          v-model="integrationRun"
        />
      </v-card-text>
    </v-card>

    <ManageIntegrationRunComponent
      v-if="showEditIntegrationRun"
      :integrationRun="integrationRun"
      v-model="showEditIntegrationRun"
      @integrationRun-saved="getDataFromApi"
    />

    <IntegrationLogsGridComponent
      v-if="showLogItems"
      :integrationRun="integrationRun"
      v-model="showLogItems"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import IntegrationRunOverviewComponent from '~/components/integration_runs/Overview'
import ManageIntegrationRunComponent from '~/components/integration_runs/ManageIntegrationRun'
import IntegrationLogsGridComponent from '~/components/integration_logs/IntegrationLogsGrid'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    IntegrationRunOverviewComponent,
    ManageIntegrationRunComponent,
    IntegrationLogsGridComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditIntegrationRun: false,
    showOverview: true,
    showLogItems: false,
    integration: {
      name: null
    },
    integrationRun: {
      task: null
    }
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.showLogItems = false
      this.integrationRun = await this.$axios.$get(`integration_runs/${this.$route.params.id}`)
      this.integration = this.integrationRun.integration
      this.loading = false
      this.showLogItems = true
    },
    editIntegrationRun () {
      this.showEditIntegrationRun = true
    },
    async deleteItem () {
      await this.$axios.$delete(`integration_runs/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    goBack () {
      this.$router.back()
    },
    refreshOverview () {
      this.showOverview = false
      this.$nextTick().then(() => {
        this.showOverview = true
      })
    },
    refreshLogItems () {
      this.showLogItems = false
      this.$nextTick().then(() => {
        this.showLogItems = true
      })
    },
    async refreshAll () {
      await this.getDataFromApi()
    }
  }
}
</script>

