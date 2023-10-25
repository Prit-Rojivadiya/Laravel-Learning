<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Tenant</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editTenant">Edit</v-btn>
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
        {{ tenant.name }}
      </v-card-title>
      <v-card-text>
        <TenantOverviewComponent
          v-if="!loading"
          :tenant="tenant"
          v-model="tenant"
        />
      </v-card-text>
    </v-card>

    <ManageTenantComponent
      v-if="showEditTenant"
      :tenant="tenant"
      v-model="showEditTenant"
      @tenant-saved="getDataFromApi"
    />

    <UsersGridComponent
      v-if="showUsers && !loading"
      :showAddUserInGrid="showAddUserInGrid"
      :scopeToTenant="tenant"
      v-model="showUsers"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import TenantOverviewComponent from '~/components/tenants/Overview'
import ManageTenantComponent from '~/components/tenants/ManageTenant'
import UsersGridComponent from '~/components/users/UsersGrid'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    TenantOverviewComponent,
    ManageTenantComponent,
    UsersGridComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditTenant: false,
    tenant: {
      name: null,
      abbrv: null
    },
    showUsers: true,
    showAddUserInGrid: false
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.tenant = await this.$axios.$get(`tenants/${this.$route.params.id}`)
      this.loading = false
    },
    editTenant () {
      this.showEditTenant = true
    },
    async deleteItem () {
      await this.$axios.$delete(`tenants/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
