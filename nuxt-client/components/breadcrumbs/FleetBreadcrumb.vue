<template>
  <v-breadcrumbs
    :items="bItems"
    :divider="bDivider"
  />
</template>

<script>

export default {
  props: ['fleetEntity', 'fleetEntityType', 'divider', 'name'],
  data: () => ({
    bItems: [],
    bDivider: '>',
    client: null,
    branch: null,
    fleet: null,
    vehicle: null,
    entityName: null,
    ro: null
  }),
  async created () {
    if (this.divider) {
      this.bDivider = this.divider
    }

    if (this.name) {
      this.entityName = this.name
    }
    else if (this.fleetEntity.name) {
      this.entityName = this.fleetEntity.name
    }

    // this.bItems.push({
    //   text: 'Dashboard',
    //   disabled: false,
    //   href: 'dashboard'
    // })

    switch (this.fleetEntityType) {
      case 'PM':
      case 'RO':
        await this.setVehicle(this.fleetEntity.vehicle_id)
        await this.setFleet(this.vehicle.fleet_id)
        await this.setBranch(this.fleet.branch_id)
        await this.setClient(this.branch.client_id)
        this.bItems.push({
          text: this.client.name,
          disabled: false,
          href: `/clients/${this.client.id}`,
        })
        this.bItems.push({
          text: this.branch.name,
          disabled: false,
          href: `/branches/${this.branch.id}`
        })
        this.bItems.push({
          text: this.fleet.name,
          disabled: false,
          href: `/fleets/${this.fleet.id}`
        })
        this.bItems.push({
          text: this.vehicle.vehicle_number,
          disabled: false,
          href: `/vehicles/${this.vehicle.id}`
        })
        // this.bItems.push({
        //   text: this.entityName,
        //   disabled: true,
        //   href: this.entityName
        // })
        break
      case 'Vehicle':
        this.vehicle = this.fleetEntity
        await this.setVehicle(this.fleetEntity.id)
        await this.setFleet(this.vehicle.fleet_id)
        await this.setBranch(this.fleet.branch_id)
        await this.setClient(this.branch.client_id)
        this.bItems.push({
          text: this.client.name,
          disabled: false,
          href: `/clients/${this.client.id}`,
        })
        this.bItems.push({
          text: this.branch.name,
          disabled: false,
          href: `/branches/${this.branch.id}`
        })
        this.bItems.push({
          text: this.fleet.name,
          disabled: false,
          href: `/fleets/${this.fleet.id}`
        })
        this.bItems.push({
          text: this.vehicle.vehicle_number,
          disabled: false,
          href: `/vehicles/${this.vehicle.id}`
        })
        break
      case 'Fleet':
        this.fleet = this.fleetEntity
        await this.setFleet(this.fleetEntity.id)
        await this.setBranch(this.fleet.branch_id)
        await this.setClient(this.branch.client_id)
        this.bItems.push({
          text: this.client.name,
          disabled: false,
          href: `/clients/${this.client.id}`,
        })
        this.bItems.push({
          text: this.branch.name,
          disabled: false,
          href: `/branches/${this.branch.id}`
        })
        this.bItems.push({
          text: this.fleet.name,
          disabled: false,
          href: `/fleets/${this.fleet.id}`
        })
        break
      case 'Branch':
        this.branch = this.fleetEntity
        await this.setBranch(this.fleetEntity.id)
        await this.setClient(this.branch.client_id)
        this.bItems.push({
          text: this.client.name,
          disabled: false,
          href: `/clients/${this.client.id}`,
        })
        this.bItems.push({
          text: this.branch.name,
          disabled: false,
          href: `/branches/${this.branch.id}`
        })
        break
      default:
        break
    }
  },
  methods: {
    async setVehicle (id) {
      let queryForVehicle = true
      if (this.fleetEntity.vehicle) {
        if (this.fleetEntity.vehicle.id && this.fleetEntity.vehicle.vehicle_number) {
          queryForVehicle = false
          this.vehicle = this.fleetEntity.vehicle
        }
      }
      if (this.vehicle) {
        if (this.vehicle.id && this.vehicle.vehicle_number) {
          queryForVehicle = false
        }
      }
      if (queryForVehicle) {
        this.vehicle = await this.$axios.$get(`vehicles/${id}`)
      }
    },
    async setFleet (id) {
      let queryForFleet = true
      if (this.fleet) {
        if (this.fleet.id && this.fleet.name) {
          queryForFleet = false
        }
      }
      if (this.vehicle) {
        if (this.vehicle.fleet) {
          if (this.vehicle.fleet.id && this.vehicle.fleet.name) {
            queryForFleet = false
            this.fleet = this.vehicle.fleet
          }
        }
      }
      if (queryForFleet) {
        this.fleet = await this.$axios.$get(`fleets/${id}`)
      }
    },
    async setBranch (id) {
      let queryForBranch = true
      if (this.branch) {
        if (this.branch.id && this.branch.name) {
          queryForBranch = false
        }
      }
      if (this.fleet) {
        if (this.fleet.branch) {
          if (this.fleet.branch.id && this.fleet.branch.name) {
            queryForBranch = false
            this.branch = this.fleet.branch
          }
        }
      }
      if (queryForBranch) {
        this.branch = await this.$axios.$get(`branches/${id}`)
      }
    },
    async setClient (id) {
      let queryForClient = true
      if (this.client) {
        if (this.client.id && this.client.name) {
          queryForClient = false
        }
      }
      if (this.branch) {
        if (this.branch.client) {
          if (this.branch.client.id && this.branch.client.name) {
            queryForClient = false
            this.client = this.branch.client
          }
        }
      }
      if (queryForClient) {
        this.client = await this.$axios.$get(`clients/${id}`)
      }
    }
  }
}
</script>
