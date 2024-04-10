(function (blocks, editor, blockEditor, components, i18n, element) {
    var el = element.createElement;
    var __ = i18n.__;
    var registerBlockType = blocks.registerBlockType;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;
    var SelectControl = components.SelectControl;

    
    registerBlockType('homelab/service-block', {
      title: __('Homelab Service Block'),
      description: __('Display a service block with configurable options.'),
      icon: 'admin-generic',
      category: 'WPHomeLab',
      attributes: {
        serviceId: {
          type: 'number',
        },
        showStatus: {
          type: 'boolean',
          default: true,
        },
        showDescription: {
          type: 'boolean',
          default: true,
        },
        showIcon: {
          type: 'boolean',
          default: true,
        },
        showImage: {
          type: 'boolean',
          default: true,
        },
        showLaunchButton: {
          type: 'boolean',
          default: true,
        },
        showTagsButton: {
          type: 'boolean',
          default: true,
        },
        showCategoryButton: {
          type: 'boolean',
          default: true,
        },
        showViewButton: {
          type: 'boolean',
          default: true,
        },
      },
      edit: function (props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        const { useState, useEffect } = wp.element;
        const [selectedService, setSelectedService] = useState(null);
        const [isLoading, setIsLoading] = useState(true);
        const [services, setServices] = useState([]);
  
        

        useEffect(() => {
          wp.apiFetch({ path: '/homelab/v1/services' })
              .then((data) => {
                  setServices(data);
                  setIsLoading(false);
              })
              .catch((error) => {
                  console.error('Error fetching services:', error);
                  setIsLoading(false);
              });
      }, []);

      useEffect(() => {
        const service = services.find(serv => serv.id === String(attributes.serviceId));
        setSelectedService(service);
        console.log(`Services: `, services);
        console.log(`ServiceId: `, attributes.serviceId);
        console.log(`Selected Service : `, service);
    }, [attributes.serviceId, services]);
  
        return [
          el(InspectorControls, { key: 'inspector' }, [
            el(PanelBody, { title: __('Service Settings'), initalOpen: true }, [
              el(SelectControl, {
                label: __('Select Service'),
                value: attributes.serviceId,
                options: [{ label: 'Select a Service value', value: '' }].concat(services.map(service => ({
                  label: service.name,
                  value: service.id,
              }))),
              onChange: (value)=>{
                console.log(`Selected Service ID: `, value);
                setAttributes({ serviceId: parseInt(value, 10) || null });},
              }),
              el(ToggleControl, {
                label: __('Show Status'),
                checked: attributes.showStatus,
                onChange: function (value) {
                  setAttributes({ showStatus: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Description'),
                checked: attributes.showDescription,
                onChange: function (value) {
                  setAttributes({ showDescription: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Icon'),
                checked: attributes.showIcon,
                onChange: function (value) {
                  setAttributes({ showIcon: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Image'),
                checked: attributes.showImage,
                onChange: function (value) {
                  setAttributes({ showImage: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Launch Button'),
                checked: attributes.showLaunchButton,
                onChange: function (value) {
                  setAttributes({ showLaunchButton: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Tags Button'),
                checked: attributes.showTagsButton,
                onChange: function (value) {
                  setAttributes({ showTagsButton: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show Category Button'),
                checked: attributes.showCategoryButton,
                onChange: function (value) {
                  setAttributes({ showCategoryButton: value });
                },
              }),
              el(ToggleControl, {
                label: __('Show View Button'),
                checked: attributes.showViewButton,
                onChange: function (value) {
                  setAttributes({ showViewButton: value });
                },
              }),
            ]),
          ]),
          el('div', { className: props.className }, 
            isLoading ? el('p', {}, __('Loading services...')) : (
                selectedService ? 
                el('div', { className: 'homelab-service-block' },
                    // Show the selected service's details
                    el('div', { className: 'homelab-service-header' },
                        attributes.showIcon && selectedService.icon && el('div', { className: 'homelab-service-icon' },
                            el('i', { className: 'fas fa-' + selectedService.icon }),
                        ),
                        attributes.showImage && selectedService.image_url && el('div', { className: 'homelab-service-image' },
                            el('img', { src: selectedService.image_url, alt: selectedService.name }),
                        ),
                        el('h3', { className: 'homelab-service-title' }, selectedService.name),
                        attributes.showStatus && selectedService.status_check && el('div', { className: 'homelab-service-status' },
                            el('span', { className: 'status-indicator ' + selectedService.status_class }),
                        ),
                    ),
                    attributes.showDescription && selectedService.description && el('div', { className: 'homelab-service-body' },
                        el('p', { className: 'homelab-service-description' }, selectedService.description),
                    ),
                    el('div', { className: 'homelab-service-footer' },
                        attributes.showLaunchButton && selectedService.service_url && el('a', {
                            className: 'homelab-service-launch-button',
                            href: selectedService.service_url,
                            target: '_blank',
                        }, __('Launch')),
                        attributes.showTagsButton && selectedService.tags && el('a', {
                            className: 'homelab-service-tags-button',
                            href: '/tags/' + selectedService.tags,
                        }, __('Posts by Tags')),
                        attributes.showCategoryButton && selectedService.category_id && el('a', {
                            className: 'homelab-service-category-button',
                            href: '/category/' + selectedService.category_id,
                        }, __('Posts by Category')),
                        attributes.showViewButton && el('a', {
                            className: 'homelab-service-view-button',
                            href: '/wp-admin/admin.php?page=homelab-view-service&service_id=' + selectedService.id,
                        }, __('View Service')),
                    ),
                ) : el('p', {}, __('Select a service to display its details here.'))
            )
        ),
          
        ];
      },
      save: function (props) {
        return null; // Block content will be rendered dynamically on the front-end
      },
    });
  })(window.wp.blocks, window.wp.editor, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.element);