import mixpanel from 'mixpanel-browser'

const mixpanelProject = mixpanel.init(
  'd817b639c2f168edcffea2979792a85a',
  { debug: false },
  'firstInstance'
)

export { mixpanelProject }
