/*
* glFusion CMS
*
* @license Copyright (c) 2005-2008, Aaron Blankstein. All rights reserved.
* Licensed under the terms of the GNU General Public License
* 		http://www.opensource.org/licenses/gpl-license.php
*
*/
function handleAdd(self, array_type, array_name) {
    if (array_type.charAt(0) == "*") {
        var nextSibling = self.nextSibling;
        while (nextSibling && nextSibling.nodeType != 1) {
            nextSibling = nextSibling.nextSibling
        }
        handleAddWithName(self, array_type, array_name, nextSibling.value);
    } else {
        handleAddWithName(self, array_type, array_name, self.parentNode.parentNode.parentNode.rows.length - 1);
    }
}

function handleAddWithName(self, array_type, array_name, name) {
    array_type = array_type.substring(1);
    if (array_type.charAt(0) == "*" || array_type.charAt(0) == "%") {
        add_array(self.parentNode.parentNode.parentNode, array_name, name, (array_type.charAt(0) == "*"), array_type, '1');
    } else if (array_type == "text") {
        add_element(self.parentNode.parentNode.parentNode, array_name, name, 'text', '', '1');
    } else if (array_type == "placeholder") {
        add_element(self.parentNode.parentNode.parentNode, array_name, name, 'hidden', '1', '1');
    } else if (array_type == "select") {
        add_select(self.parentNode.parentNode.parentNode, array_name, name - 1, '1');
    }
}

function add_select(tbl, arr_name, index, deletable) {
    var newRow = tbl.insertRow(tbl.rows.length - 1);
    titleCell = newRow.insertCell(0);
    paramCell = newRow.insertCell(1);
    titleCell.className = "alignright";
    titleCell.appendChild(document.createTextNode(index));
    dropDown = tbl.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].getElementsByTagName('select')[0].cloneNode(true);
    dropDown.name = arr_name + "[" + index + "]";
    paramCell.appendChild(dropDown);

    if (deletable) {
        paramCell.appendChild(document.createTextNode("\n"));
        deleteButton = document.createElement("input");
        deleteButton.type = "button";
        deleteButton.value = "x";
        deleteButton.onclick =
            function () {
                glfremove(this)
            };
        paramCell.appendChild(deleteButton);
    }
}

function add_element(tbl, arr_name, index, disp_type, def_val, deletable) {
    var newRow = tbl.insertRow(tbl.rows.length - 1);
    titleCell = newRow.insertCell(0);
    paramCell = newRow.insertCell(1);
    titleCell.className = "alignleft";
    titleCell.appendChild(document.createTextNode(index));
    inputBox = document.createElement("input");
    inputBox.type = disp_type;
    inputBox.name = arr_name + "[" + index + "]";
    inputBox.value = def_val;
    inputBox.classList.add('uk-form-width-medium');
    inputBox.setAttribute("style", "width:80%;padding-right:5px;");
    paramCell.appendChild(inputBox);

    if (deletable) {
        //        deleteButton = document.createElement("input");
        deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.classList.add('uk-button');
        deleteButton.classList.add('uk-button-small');
        deleteButton.classList.add('uk-button-danger');
        deleteButton.value = "x";
        deleteButton.innerHTML = 'x';
        deleteButton.text = 'X';
        deleteButton.onclick =
            function () {
                glfremove(this)
            };
        paramCell.appendChild(deleteButton);
    }
}
function glfremove(self) {
    cell = self.parentNode.parentNode;
    cell.parentNode.removeChild(cell);
}

function add_array(tbl, arr_name, arr_index, key_names, arr_type, deletable) {
    var newRow = tbl.insertRow(tbl.rows.length - 1);
    labelCell = newRow.insertCell(0);
    arrayCell = newRow.insertCell(1);

    labelCell.appendChild(document.createTextNode(arr_index));
    labelCell.className = "alignright";

    arrLink = document.createElement("input");
    arrLink.type = "button";
    arrLink.onclick =
        function () {
            hide_show_tbl(selectChildByID(this.parentNode, 'arr_table'), this);
        };
    arrLink.value = "+";
    arrayCell.appendChild(arrLink);

    ele_place_holder = document.createElement("input");
    ele_place_holder.type = "hidden";
    ele_place_holder.name = arr_name + "[" + arr_index + "][placeholder]";
    ele_place_holder.value = "true";
    arrayCell.appendChild(ele_place_holder);

    arrayCell.appendChild(document.createTextNode(" "));

    if (deletable) {
        deleteButton = document.createElement("input");
        deleteButton.type = "button";
        deleteButton.value = "x";
        deleteButton.onclick = function () {
            glfremove(this);
        };
        arrayCell.appendChild(deleteButton);
    }

    arrTable = document.createElement("table");
    arrTable.style.display = "none";
    arrTable.id = "arr_table";

    add_ele_cell = arrTable.insertRow(0).insertCell(0);
    add_ele_cell.colspan = 2;
    add_ele_press = document.createElement("input");
    add_ele_press.type = "button";
    add_ele_press.value = "Add Element";

    if (!key_names) {
        add_ele_press.onclick = function () {
            handleAdd(this, arr_type, arr_name + "[" + arr_index + "]");
        };
        add_ele_cell.appendChild(add_ele_press);
    } else {
        add_ele_press.onclick = function () {
            handleAdd(this, arr_type, arr_name + "[" + arr_index + "]");
        };

        add_ele_cell.appendChild(add_ele_press);
        arr_index_box = document.createElement("input");
        arr_index_box.type = "text";
        arr_index_box.style.width = "65px";
        add_ele_cell.appendChild(arr_index_box);
    }

    arrayCell.appendChild(arrTable);
}

function hide_show_tbl(tbl, button) {
    tbl.style.display = (tbl.style.display != 'none' ? 'none' : '');
    button.value = (button.value != '+' ? '+' : '-');
    return false;
}

function open_group(group_var) {
    document.group.conf_group.value = group_var;
    document.group.submit();
    return false;
}

function open_subgroup(group_var, sg_var) {
    document.group.conf_group.value = group_var;
    document.group.subgroup.value = sg_var;
    document.group.submit();
    return false;
}

function selectChildByID(parent, ID) {
    for (i = 0; i < parent.childNodes.length; i++) {
        child = parent.childNodes[i];
        if (child.id == ID) {
            return child;
        }
    }
}

function restore(param) {
    document.group.subgroup.value = document.subgroup.sub_group.value;
    action = document.createElement("INPUT");
    action.setAttribute("value", "restore");
    action.setAttribute("name", "set_action");
    action.setAttribute("type", "hidden");
    namev = document.createElement("INPUT");
    namev.setAttribute("value", param);
    namev.setAttribute("type", "hidden");
    namev.setAttribute("name", "name");
    document.group.appendChild(namev);
    document.group.appendChild(action);
    document.group.submit();
}

function unset(param) {
    document.group.subgroup.value = document.subgroup.sub_group.value;
    action = document.createElement("INPUT");
    action.setAttribute("value", "unset");
    action.setAttribute("name", "set_action");
    action.setAttribute("type", "hidden");
    namev = document.createElement("INPUT");
    namev.setAttribute("value", param);
    namev.setAttribute("type", "hidden");
    namev.setAttribute("name", "name");
    document.group.appendChild(namev);
    document.group.appendChild(action);
    document.group.submit();
}