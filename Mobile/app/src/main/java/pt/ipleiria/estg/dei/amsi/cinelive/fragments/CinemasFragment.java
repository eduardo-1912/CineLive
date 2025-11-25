package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Toast;

import java.util.Arrays;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.CinemasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentCinemasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.NetworkUtils;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link CinemasFragment} factory method to
 * create an instance of this fragment.
 */
public class CinemasFragment extends Fragment {
    private FragmentCinemasBinding binding;
    private PreferencesManager preferences;
    private CinemasAdapter adapter;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentCinemasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        // Aceder às preferences
        preferences = new PreferencesManager(requireContext());

        // Obter cinema selecionado
        int cinemaSelecionado = preferences.getCinemaId();

        List<Cinema> cinemas = Arrays.asList(
                new Cinema(1, "Cinema Leiria", "Rua das Flores N5, Leiria", "123456789", "leiria@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares"),
                new Cinema(2, "Cinema Coimbra", "Av. Fernão Magalhães, Coimbra", "123456789", "coimbra@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares"),
                new Cinema(3, "Cinema Lisboa", "Rua Augusta 115, Lisboa", "123456789", "lisboa@cinelive.pt", "10:00 - 23:00", "12 Salas • 800 Lugares")
        );

        adapter = new CinemasAdapter(
            cinemas, cinemaSelecionado,
            cinema -> {
                preferences.setCinemaId(cinema.getId());
                adapter.setCinemaSelecionado(cinema.getId());
            }
        );

        binding.rvCinemas.setLayoutManager(new LinearLayoutManager(getContext()));
        binding.rvCinemas.setAdapter(adapter);
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}