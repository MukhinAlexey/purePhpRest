function sendTask(){

	var text = $('#input-task').val();
	var status = "undone";
	var token = localStorage.getItem("token");

	$.ajax({
		url:"../api.php?action=addtask",
		method:"POST",
		dataType: 'json',
		cache:false,
		data:{
			text:text,
			status:status,
			token:token
		},
		success:function(){
			getTasks();
		},
		error:function(){ 
		}
	});
};

function getTasks(){
	var token = localStorage.getItem("token");

	$.ajax({ 
		url: "../api.php?action=gettasks",
		method:"GET",
		dataType: 'json',
		cache:false,
		data:{
			token:token
		},
        success: function(data){
           fillTasksList(data);	
        }
    });
};

function deleteTask(id){
	var token = localStorage.getItem("token");

	$.ajax({ 
		url: "../api.php?action=deletetask",
		method:"POST",
		dataType:'json',
		cache:false,
		data:{
			token:token,
			id:id
		},
        success: function(){
           getTasks();	
        }
    });
};

$(document).ready(function(){
	$('#logout').click(function(){
		$.ajax({
			url:"../api.php?action=logout",
			method:"GET",
			dataType: 'json',
			cache:false,
			success:function(){
				localStorage.removeItem('token');
				window.location="auth.php";
			},
			error:function(){ 
			}
		});
	});
});

$(document).ready(function(){
	getTasks();
});

function fillTasksList(data){

	var taskLi, taskChkbx, taskVal, taskBtn, taskTrsh;

	var tasklist = document.getElementById("tasks");
	$('#tasks').empty();
	var tasklistChildren = tasklist.children;

	for (i = 0; i < data.length; i += 1) {
		taskLi = document.createElement("li");
		taskLi.setAttribute("class", "task");
		taskLi.setAttribute("id", "task-id " + data[i]["id_task"]);
		//CHECKBOX
		taskChkbx = document.createElement("input");
		taskChkbx.setAttribute("type", "checkbox");
		//USER TASK
		taskVal = document.createTextNode(data[i]["text"]);
		//DELETE BUTTON
		taskBtn = document.createElement("button");
		taskBtn.setAttribute("id", "task-button-id " + data[i]["id_task"]);
		var id_task = data[i]["id_task"];
		
		taskBtn.addEventListener("click", function(){ deleteTask(id_task) });

		//taskBtn.onclick(deleteTask(id_task));

		//TRASH ICON
		taskTrsh = document.createElement("i");
		taskTrsh.setAttribute("class", "fa fa-trash");
		//INSTERT TRASH CAN INTO BUTTON
		taskBtn.appendChild(taskTrsh);

		//APPEND ELEMENTS TO TASKLI
		taskLi.appendChild(taskChkbx);
		taskLi.appendChild(taskVal);
		taskLi.appendChild(taskBtn);
	
		tasklist.appendChild(taskLi);
	}
};

$(document).ready(function(){
	$('#logout').click(function(){
		$.ajax({
			url:"../api.php?action=logout",
			method:"GET",
			dataType: 'json',
			cache:false,
			success:function(){
				localStorage.removeItem('token');
				window.location="auth.php";
			},
			error:function(){ 
			}
		});
	});
});


(function() {
	'use strict';
	var tasker = {
		init: function() {
			this.cacheDom();
			this.bindEvents();
			this.evalTasklist();
		},
		cacheDom: function() {
			this.taskInput = document.getElementById("input-task");
			this.addBtn = document.getElementById("add-task-btn");
			this.tasklist = document.getElementById("tasks");
			this.tasklistChildren = this.tasklist.children;
			this.errorMessage = document.getElementById("error");
		},
		bindEvents: function() {
			this.addBtn.onclick = this.addTask.bind(this);
			this.taskInput.onkeypress = this.enterKey.bind(this);
		},
		evalTasklist: function() {
			var i, chkBox, delBtn;
			//BIND CLICK EVENTS TO ELEMENTS
			for (i = 0; i < this.tasklistChildren.length; i += 1) {
				//ADD CLICK EVENT TO CHECKBOXES
				chkBox = this.tasklistChildren[i].getElementsByTagName("input")[0];
				chkBox.onclick = this.completeTask.bind(this, this.tasklistChildren[i], chkBox);
				//ADD CLICK EVENT TO DELETE BUTTON
				delBtn = this.tasklistChildren[i].getElementsByTagName("button")[0];
				delBtn.onclick = this.delTask.bind(this, i);
			}
		},
		completeTask: function(i, chkBox) {
			if (chkBox.checked) {
				i.className = "task completed";
			} else {
				this.incompleteTask(i);
			}
		},
		incompleteTask: function(i) {
			i.className = "task";
		},
		enterKey: function(event) {
			if (event.keyCode === 13 || event.which === 13) {
				this.addTask();
			}
		},
		addTask: function() {
			var value = this.taskInput.value;
			this.errorMessage.style.display = "none";

			if (value === "") {
				this.error();
			} else {
				sendTask();
				this.taskInput.value = "";
			}
		},
		delTask: function(i) {
			console.log(i);
			deleteTask(i);
			this.evalTasklist();
		},
		error: function() {
			this.errorMessage.style.display = "block";
		}
	};

	tasker.init();
}());


