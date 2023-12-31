import { useAtom } from 'jotai';
import { useState } from '@wordpress/element';
import { CustomSelectControl, Button } from '@wordpress/components';
import {
  Row,
  RowKey,
  RowValue,
  RowValueUndo,
  RowValueUndoIn,
  RowState,
} from './Row';
import {
  selectedIndexDataAtom,
  selectedIndexDataComponentAtom,
} from '../../../atoms/admin';
import { getInterventionKey, getValue } from '../../../utils/admin';
import { __ } from '../../../utils/wp';

/**
 * Get Option Label
 *
 * @param {string} value
 * @returns {string}
 */
const getOptionLabel = (value) => {
  return value
    .replaceAll('-', ' ')
    .split('.')
    .map((str) => {
      return str
        .split(' ')
        .map((word) => word[0].toUpperCase() + word.substring(1))
        .join(' ');
    })
    .join('/');
};

/**
 * Routing Options
 *
 * @description get `pagenow` options that include `.`.
 */
const routingOptions = intervention.route.admin.data.pagenow
  .filter((value) => {
    return value.includes('.');
  })
  .filter(Boolean);

/**
 * Routing Options Select Control
 *
 * @description format `routingOptions` for WordPress `<SelectControl>` component.
 */
const routingOptionsSelectControl = routingOptions.map((value) => {
  const name = getOptionLabel(value);
  return { key: name, name, value };
});

/**
 * Options All
 *
 * @description create blank entry item and merge with `routingOptionsSelectControl`.
 */
const optionsAll = [
  { key: '', name: '', value: '' },
  ...routingOptionsSelectControl,
];

/**
 * Is Route
 *
 * @param {string} k
 * @returns {boolean}
 */
const isRouteItem = (k) => {
  return k.includes(':route');
};

/**
 * Route Item
 *
 * @param {object} { key: {string} key }
 * @returns <RouteItem />
 */
const RouteItem = ({ item: key, children }) => {
  const interventionKey = getInterventionKey(key);
  const [data] = useAtom(selectedIndexDataAtom);
  const [, setComponent] = useAtom(selectedIndexDataComponentAtom);
  const [v, immutable] = getValue(data.components, interventionKey);
  const value = v === true ? 'dashboard.home' : v;
  const init = v === false ? '' : value;
  const [state, setState] = useState(init);

  /**
   * Immutable Option
   *
   * @description only return the hard coded option.
   *
   * @param {string} value
   * @returns {array}
   */
  const immutableOption = (value) => {
    const name = getOptionLabel(value);
    return [{ key: name, name, value }];
  };

  /**
   * Excl Key From Options
   *
   * @description remove routes that start with this `key`.
   *
   * @param {array} options
   * @returns {array}
   */
  const exclKeyFromOptions = (options) => {
    return options
      .filter((item) => {
        return item.value.startsWith(interventionKey) === false;
      })
      .filter(Boolean);
  };

  /**
   * Options
   *
   * @description resolve correct options.
   */
  const options = immutable
    ? immutableOption(state)
    : exclKeyFromOptions(optionsAll);

  /**
   * Handler
   *
   * @param {string} value
   */
  const handler = (selected) => {
    const value = selected !== '' ? selected.selectedItem.value : '';

    value !== ''
      ? setComponent(['add', interventionKey, value])
      : setComponent(['del', interventionKey]);

    setState(value);
  };

  /**
   * Render
   */
  return (
    <>
      <Row item={key} immutable={immutable}>
        <RowState state={state} />
        <RowKey>{interventionKey}</RowKey>
        <RowValue>
          <CustomSelectControl
            className="row"
            label="Route"
            hideLabelFromVision={true}
            value={options.find((option) => option.value === state)}
            options={options}
            onChange={(route) => handler(route)}
          />

          {immutable === false && state !== '' && (
            <RowValueUndo>
              <Button className="is-secondary" onClick={() => handler('')}>
                <RowValueUndoIn />
              </Button>
            </RowValueUndo>
          )}
        </RowValue>
      </Row>

      {state === '' && children}
    </>
  );
};

export { isRouteItem, RouteItem };
