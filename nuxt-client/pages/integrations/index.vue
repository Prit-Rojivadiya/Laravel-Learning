<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Integrations</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addIntegration">Add Integration</v-btn>
        </v-col>
      </v-row>
    </div>

    <IntegrationsGridComponent
      v-if="showIntegrations"
      :showAddIntegrationInGrid="showAddIntegrationInGrid"
      v-model="showIntegrations"
    />

    <ManageIntegrationComponent
      v-if="addingIntegration"
      v-model="addingIntegration"
      @integration-saved="refresh"
    />

  </div>
</template>

<script>

import IntegrationsGridComponent from '~/components/integrations/IntegrationsGrid'
import ManageIntegrationComponent from '~/components/integrations/ManageIntegration'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    IntegrationsGridComponent,
    ManageIntegrationComponent
  },
  data: () => ({
    addingIntegration: false,
    showIntegrations: true,
    showAddIntegrationInGrid: false,
  }),
  methods: {
    addIntegration () {
      this.addingIntegration = true
    },
    refresh () {
      this.showIntegrations = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showIntegrations = true
      })
    }
  }
}
</script>
