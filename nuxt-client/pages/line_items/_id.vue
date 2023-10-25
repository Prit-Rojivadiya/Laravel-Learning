<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Line Item</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editLineItem">Edit</v-btn>
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
        {{ line_item_type_name }} | {{ line_item_category_name }}
      </v-card-title>
      <v-card-text>
        <LineItemOverviewComponent
          v-if="!loading"
          :line_item="line_item"
          v-model="line_item"
        />
      </v-card-text>
    </v-card>

    <ManageLineItemComponent
      v-if="showEditLineItem"
      :line_item="line_item"
      :repair_order="line_item.repair_order"
      :allowVehicleAssignment="allowVehicleAssignment"
      v-model="showEditLineItem"
      @line_item-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import LineItemOverviewComponent from '~/components/line_items/Overview'
import ManageLineItemComponent from '~/components/line_items/ManageLineItem'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    LineItemOverviewComponent,
    ManageLineItemComponent,
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditLineItem: false,
    allowVehicleAssignment: false,
    showLineItems: false,
    showAddLineItemInGrid: true,
    showLineItemInGrid: false,
    line_item: {
      desc: null,
    },
    line_item_type_name: null,
    line_item_category_name: null,
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.line_item = await this.$axios.$get(`line_items/${this.$route.params.id}`)
      this.line_item.line_item_type = await this.$axios.$get(`line_item_types/${this.line_item.line_item_category.line_item_type_id}`)
      this.line_item_category_name = this.line_item.line_item_category.name
      this.line_item_type_name = this.line_item.line_item_type.name
      this.loading = false
      this.showLineItems = true
    },
    editLineItem () {
      this.showEditLineItem = true
    },
    async deleteItem () {
      await this.$axios.$delete(`line_items/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
