<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Integrations
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddIntegrationInGrid && pCreateEditDelete" small color="info" style="float: right" class="my-4 mr-3" @click="addIntegrationAction">Add Integration</v-btn>
      </v-col>
    </v-row>

    <v-data-table
      :headers="headers"
      :loading="loading"
      :items="tableData"
      item-key="id"
      :items-per-page="100"
      :footer-props="{ itemsPerPageOptions: [50, 100, 200, 500, -1] }"
      :server-items-length="totalItems"
      dense
      @update:options="getDataFromApi"
      @click:row="openData"
    >
      <template v-slot:item.created_at="{ item, value }">
        <template v-if="value">{{ value | formatDateMDY }}</template>
      </template>
      <template v-slot:item.updated_at="{ item, value }">
        {{ value | formatDateMDY }}
      </template>
      <template v-slot:item.actions="{ item }">
        <v-tooltip top>
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              small
              class="mr-2"
              v-bind="attrs"
              v-on="on"
            >
              mdi-pencil
            </v-icon>
          </template>
          <span>Manage Integration</span>
        </v-tooltip>
      </template>

    </v-data-table>

    <ManageIntegrationComponent
      v-if="addIntegration"
      v-model="addIntegration"
      :client="client"
      @integration-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ManageIntegrationComponent from '~/components/integrations/ManageIntegration'

export default {
  name: 'IntegrationGrid',
  props: ['value', 'client', 'showAddIntegrationInGrid'],
  components: {
    ManageIntegrationComponent
  },
  data: () => ({
    loading: true,
    addIntegration: false,
    tableData:[],
    totalItems: 0,
    options: {page: 1},
    pCreateEditDelete: false,
    integrations: null,
  }),
  created () {
    if (this.$laravel.hasPermission('manage any integration')) {
      this.pCreateEditDelete = true
    }
  },
  computed: {
    headers () {
      let headers = []
      headers.push({ text: 'Name', align: 'left', value: 'name' })
      if (!this.client) {
        headers.push({ text: 'Account', align: 'left', value: 'client.name' })
      }
      headers.push({ text: 'Enabled', align: 'left', value: 'active' })
      // headers.push({ text: 'Created Date', align: 'left', value: 'created_at' })
      // headers.push({ text: 'Updated Date', align: 'left', value: 'updated_at' })
      headers.push({ text: 'Actions', value: 'actions', sortable: false })
      return headers
    },
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.tableData = []

      if (options) {
        this.options = options
      } else {
        options = { sortBy: null, sortDesc: null }
      }

      const response = await this.$axios.$get('integrations', {
        params: {
          filterByClient: (this.client) ? this.client.id : null
        }
      })

      this.integrations = response.data
      this.totalItems = response.total

      for (let item of this.integrations) {
        if (item.active) {
          item.active = true
        }
        else {
          item.active = false
        }
        this.tableData.push(item)
      }

      this.loading = false
    },
    openData (dataItem) {
      this.$router.push(`/integrations/${dataItem.id}`)
    },
    addIntegrationAction() {
      this.addIntegration = true
    }
  }
}
</script>

<style scoped>

</style>
