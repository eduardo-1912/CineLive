package pt.ipleiria.estg.dei.amsi.cinelive;

import android.content.Intent;
import android.os.Bundle;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityLoginBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityMainBinding;

public class LoginActivity extends AppCompatActivity {

    ActivityLoginBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityLoginBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Seta voltar atrás
        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        binding.btnSignup.setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, SignupActivity.class));
            finish();
        });


    }

    // Voltar atrás
    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }

}