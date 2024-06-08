package com.example.foodapp.ui.screens

import android.annotation.SuppressLint
import android.content.Context
import android.os.Build
import android.util.Log
import androidx.annotation.RequiresApi
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.IconButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Send
import androidx.compose.material.Card
import androidx.compose.material.Surface
import androidx.compose.material.icons.filled.AccountCircle
import androidx.compose.material3.BottomAppBar
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TextFieldDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.livedata.observeAsState
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.unit.dp
import com.example.foodapp.viewmodel.ChatViewModel
import com.example.foodapp.viewmodel.MessageItem
import org.ocpsoft.prettytime.PrettyTime
import java.text.SimpleDateFormat
import java.time.LocalDateTime
import java.time.format.DateTimeFormatter
import java.util.Date
import java.util.Locale
import java.util.TimeZone


@OptIn(ExperimentalMaterial3Api::class)
@RequiresApi(Build.VERSION_CODES.O)
@SuppressLint("UnusedMaterial3ScaffoldPaddingParameter")
@Composable
fun ChatScreen(
    viewModel: ChatViewModel,
    onBackClicked: () -> Unit
) {
    val context  = LocalContext.current
    val messages by viewModel.messagesLiveData.observeAsState(listOf())
    val sharedPreferences = context.getSharedPreferences("customer_data", Context.MODE_PRIVATE)
    val customerId  = sharedPreferences.getInt("customer_id",-1)
    var textState by remember { mutableStateOf("") }
    val listState = rememberLazyListState()
    LaunchedEffect(key1 = messages.size) {
        listState.scrollToItem(0)
    }


    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Nhắn với admin") },
                navigationIcon = {
                    IconButton(onClick = onBackClicked) {
                        Icon(imageVector = Icons.Default.ArrowBack, contentDescription = "Back")
                    }
                }
            )
        },
        bottomBar = {
            BottomAppBar(
                containerColor = Color.Transparent,
                tonalElevation = 0.dp
            ) {
                Surface(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 8.dp),
                    shape = RoundedCornerShape(16.dp), // Làm bo tròn góc
                    color = Color.White // Màu nền trắng
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        IconButton(
                            onClick = { /* Xử lý hành động của biểu tượng ở đầu */ }
                        ) {
                            Icon(
                                imageVector = Icons.Default.AccountCircle,
                                contentDescription = "User Icon",
                                tint = Color.Gray
                            )
                        }
                        OutlinedTextField(
                            value = textState,
                            onValueChange = { textState = it },
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(50.dp)
                                .padding(end = 8.dp), // Để biểu tượng ở cuối không đè lên văn bản
                            placeholder = { Text("Nhập tin nhắn...") },
                            colors = TextFieldDefaults.outlinedTextFieldColors(
                                focusedBorderColor = Color.Transparent, // Bỏ viền khi được focus
                                unfocusedBorderColor = Color.Transparent // Bỏ viền khi không được focus
                            ),
                            keyboardOptions = KeyboardOptions.Default.copy(imeAction = ImeAction.Send),
                            keyboardActions = KeyboardActions(
                                onSend = {
                                    if (textState.isNotEmpty()) {
                                        viewModel.sendMessage(
                                            MessageItem(
                                                content = textState,
                                                id = 1,
                                                sender_id = customerId,
                                                sender_type = "customer",
                                                created_at = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss.SSS"))
                                            )
                                        )
                                        textState = "" // Xóa nội dung sau khi gửi
                                    }
                                }
                            ),
                            singleLine = true,
                            trailingIcon = {
                                IconButton(
                                    onClick = {
                                        if (textState.isNotEmpty()) {
                                            viewModel.sendMessage(
                                                MessageItem(
                                                    content = textState,
                                                    id = 1,
                                                    sender_id = customerId,
                                                    sender_type = "customer",
                                                    created_at = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss.SSS"))
                                                )
                                            )
                                            textState = ""
                                        }
                                    }
                                ) {
                                    Icon(imageVector = Icons.Default.Send, contentDescription = "Send")
                                }
                            }
                        )
                    }
                }
            }


        }
    ) { paddingValues ->
        LazyColumn(
            state = listState,
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = paddingValues.calculateTopPadding(), bottom = paddingValues.calculateBottomPadding()),
            reverseLayout = true,
            verticalArrangement = Arrangement.spacedBy(4.dp)
        ) {
            itemsIndexed(messages) { _, message ->
                MessageRow(message, isCurrentUser = message.sender_type == "customer")
            }
        }
    }
}

@Composable
fun MessageRow(message: MessageItem, isCurrentUser: Boolean) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 8.dp, vertical = 4.dp),
        horizontalArrangement = if (isCurrentUser) Arrangement.End else Arrangement.Start
    ) {
        Card(
            modifier = Modifier
                .widthIn(max = 320.dp)
                .padding(4.dp),
            shape = RoundedCornerShape(16.dp),
            elevation = 10.dp,
            backgroundColor = if (isCurrentUser) MaterialTheme.colorScheme.primary else MaterialTheme.colorScheme.surface
        ) {
            Column(modifier = Modifier.padding(12.dp)) {
                val timeAgo = convertToTimeAgo(message.created_at)
                Text(
                    text = message.content,
                    style = MaterialTheme.typography.bodyMedium,
                    color = if (isCurrentUser) MaterialTheme.colorScheme.onPrimary else MaterialTheme.colorScheme.onSurface
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = timeAgo,
                    style = MaterialTheme.typography.bodySmall,
                    color = if (isCurrentUser) MaterialTheme.colorScheme.onPrimary.copy(alpha = 0.7f) else MaterialTheme.colorScheme.onSurface.copy(alpha = 0.7f)
                )
            }
        }
    }
}


fun convertToTimeAgo(dateTimeString: String): String {
    // Determine the input format based on the dateTimeString
    val inputFormat = if (dateTimeString.contains("T")) {
        SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'", Locale.getDefault()).apply {
            timeZone = TimeZone.getTimeZone("UTC")
        }
    } else {
        SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SSS", Locale.getDefault())
    }

    // Parse the input date string to a Date object
    val date = inputFormat.parse(dateTimeString)

    // Get the current time and calculate the time difference in milliseconds
    val currentTime = System.currentTimeMillis()
    val timeDifference = currentTime - (date?.time ?: 0)

    // Calculate time units in terms of milliseconds
    val secondMillis = 1000
    val minuteMillis = secondMillis * 60
    val hourMillis = minuteMillis * 60
    val dayMillis = hourMillis * 24
    val weekMillis = dayMillis * 7
    val monthMillis = dayMillis * 30
    val yearMillis = dayMillis * 365

    return when {
        timeDifference < minuteMillis -> "ngay bây giờ"
        timeDifference < 2 * minuteMillis -> "1 phút trước"
        timeDifference < 50 * minuteMillis -> "${timeDifference / minuteMillis} phút trước"
        timeDifference < 90 * minuteMillis -> "1 giờ trước"
        timeDifference < 24 * hourMillis -> "${timeDifference / hourMillis} giờ trước"
        timeDifference < 48 * hourMillis -> "ngày hôm qua"
        timeDifference < 7 * dayMillis -> "${timeDifference / dayMillis} ngày trước"
        timeDifference < 2 * weekMillis -> "tuần trước"
        timeDifference < monthMillis -> "${timeDifference / weekMillis} tuần trước"
        timeDifference < 2 * monthMillis -> "1 tháng trước"
        timeDifference < yearMillis -> {
            val months = timeDifference / monthMillis
            if (months <= 1) "1 tháng trước" else "$months tháng trước"
        }
        else -> {
            val years = timeDifference / yearMillis
            if (years <= 1) "1 năm trước" else "$years năm trước"
        }
    }
}

