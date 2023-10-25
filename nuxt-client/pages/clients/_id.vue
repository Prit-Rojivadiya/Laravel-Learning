<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Account</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editClient"  v-if="pCreateEditDelete">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
            v-if="pCreateEditDelete"
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
        {{ client.name }}
      </v-card-title>
      <v-card-text>
        <ClientOverviewComponent
          v-if="!loading"
          :client="client"
          :integrations="integrations"
          v-model="client"
        />
      </v-card-text>
    </v-card>

    <ManageClientComponent
      v-if="showEditClient"
      :client="client"
      :integrations="integrations"
      v-model="showEditClient"
      @client-saved="getDataFromApi"
    />

    <v-tabs v-model="default_tab" show-arrows class="mt-6">
      <v-tabs-slider color="teal lighten-3"></v-tabs-slider>
      <v-tab key="branches">Branches</v-tab>
      <v-tab key="integrations">Integrations</v-tab>
      <v-tab-item key="branches">
        <v-card-text>
          <BranchesGridComponent
            v-if="showBranches"
            :client="client"
            :showAddBranchInGrid="showAddBranchInGrid"
            v-model="showBranches"
          />
        </v-card-text>
      </v-tab-item>
      <v-tab-item key="integrations">
        <v-card-text>
          <IntegrationsGridComponent
            v-if="showIntegrations"
            :client="client"
            :showAddIntegrationInGrid="showAddIntegrationInGrid"
            v-model="showIntegrations"
          />
        </v-card-text>
      </v-tab-item>
    </v-tabs>

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import ClientOverviewComponent from '~/components/clients/Overview'
import ManageClientComponent from '~/components/clients/ManageClient'
import BranchesGridComponent from '~/components/branches/BranchesGrid'
import IntegrationsGridComponent from '~/components/integrations/IntegrationsGrid'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    ClientOverviewComponent,
    ManageClientComponent,
    BranchesGridComponent,
    IntegrationsGridComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditClient: false,
    showBranches: false,
    showAddBranchInGrid: true,
    showAddIntegrationInGrid: true,
    showIntegrations: false,
    client: {
      name: null,
      abbrv: null,
    },
    integrations: [],
    pCreateEditDelete: false
  }),
  async created () {
    if (this.$laravel.hasPermission('manage any client')) {
      this.pCreateEditDelete = true
    }
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi (options) {
      this.loading = true
      this.client = await this.$axios.$get(`clients/${this.$route.params.id}`)
      // show enabled integrations
      const response = await this.$axios.$get('integrations', {
        params: {
          filterByClient: (this.client) ? this.client.id : null
        }
      })
      this.integrations = response.data
      this.loading = false
      this.showBranches = true // Causes BranchesGridComponent to render after client is retrieved
      this.showIntegrations = true
    },
    editClient () {
      this.showEditClient = true
    },
    async deleteItem () {
      await this.$axios.$delete(`clients/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
