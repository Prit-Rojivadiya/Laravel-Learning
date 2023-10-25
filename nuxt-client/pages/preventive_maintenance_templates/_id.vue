<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Preventive Maintenance Template</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editPreventiveMaintenanceTemplate">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
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
        {{ preventive_maintenance_template.name }}
      </v-card-title>
      <v-card-text>
        <PreventiveMaintenanceTemplateOverviewComponent
          v-if="!loading"
          :preventive_maintenance_template="preventive_maintenance_template"
          v-model="preventive_maintenance_template"
        />
      </v-card-text>
    </v-card>

    <ManagePreventiveMaintenanceTemplateComponent
      v-if="showEditPreventiveMaintenanceTemplate"
      :preventive_maintenance_template="preventive_maintenance_template"
      v-model="showEditPreventiveMaintenanceTemplate"
      @preventive_maintenance_template-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import PreventiveMaintenanceTemplateOverviewComponent from '~/components/preventive_maintenance_templates/Overview'
import ManagePreventiveMaintenanceTemplateComponent from '~/components/preventive_maintenance_templates/ManagePreventiveMaintenanceTemplate'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    PreventiveMaintenanceTemplateOverviewComponent,
    ManagePreventiveMaintenanceTemplateComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditPreventiveMaintenanceTemplate: false,
    preventive_maintenance_template: {
      name: null,
      desc: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.preventive_maintenance_template = await this.$axios.$get(`preventive_maintenance_templates/${this.$route.params.id}`)
      this.loading = false
    },
    editPreventiveMaintenanceTemplate () {
      this.showEditPreventiveMaintenanceTemplate = true
    },
    async deleteItem () {
      await this.$axios.$delete(`preventive_maintenance_templates/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
